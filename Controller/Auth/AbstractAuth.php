<?php

namespace LoganStellway\Social\Controller\Auth;

/**
 * Dependencies
 */
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use LoganStellway\Social\Helper\Config;
use LoganStellway\Social\Model\Service;
use LoganStellway\Social\Model\Session;
use LoganStellway\Social\Model\CustomerFactory;

/**
 * Abstract Authorization Action
 */
abstract class AbstractAuth extends Action implements AuthInterface
{
    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    protected $_cookieMetadataManager;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Service
     */
    protected $_service;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param Context         $context
     * @param ResultFactory   $resultFactory
     * @param Config          $config
     * @param Service         $service
     * @param Session         $customerSession
     * @param Customer        $customer
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Config $config,
        Service $service,
        Session $customerSession,
        CustomerFactory $customerFactory
    ) {
        parent::__construct($context);

        $this->_resultFactory = $resultFactory;
        $this->_config = $config;
        $this->_service = $service;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->_cookieMetadataFactory) {
            $this->_cookieMetadataFactory = ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }
        return $this->_cookieMetadataFactory;
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->_cookieMetadataManager) {
            $this->_cookieMetadataManager = ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }
        return $this->_cookieMetadataManager;
    }

    /**
     * Redirect
     * 
     * @param  string $url
     * @param  boolean $external
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirect($url, bool $external = false)
    {
        $redirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $external ? $redirect->setUrl($url) : $redirect->setPath($url);
    }

    /**
     * Get service name
     * @return string|null
     */
    protected function getServiceName()
    {
        $identifier = trim($this->getRequest()->getPathInfo(), '/');
        $path = explode('/', $identifier);
        return isset($path[2]) ? $path[2] : null;
    }

    /**
     * Get access token
     * @param  string $code
     * @param  string $state
     * @return string|boolean
     */
    public function getAccessToken($code, $state = null)
    {
        try {
            $token = $this->service->requestAccessToken($code, $state);
            return $token;
        } catch (\Exception $e) {
            $this->addMessage($e->getMessage());
            return false;
        }
    }

    /**
     * Add global message
     * @param  string $key
     * @return string|null
     */
    protected function addMessage($message = '', $type = MessageInterface::TYPE_ERROR)
    {
        if ($message) {
            $this->messageManager->addUniqueMessages([
                $this->messageManager->createMessage(
                    $type
                )->setText($message)
            ]);
        }
    }

    /**
     * Get path from URL string
     * @param  string $url
     * @return string
     */
    public function getPathFromUrl(string $url)
    {
        $base = explode('://', $this->_url->getUrl());
        $url = explode($base[1], $url);

        if (isset($url[1]) && $url[1] != '/') {
            return trim($url[1], '/');
        }
        return '/';
    }

    /**
     * Proceed to social action
     * @param  \Magento\Framework\DataObject $data
     */
    protected function _auth(\Magento\Framework\DataObject $data)
    {
        $this->_customerSession->setSocialData($data->getData());
        // $this->_customerSession->unsSocialAction();

        if ($data->getUserId()) {
            // Connect or disconnect account
            if ($this->_customerSession->getSocialAction() == 'connect') return $this->connect();

            $user = $this->_customerFactory->create()->loadByIdType(
                $data->getUserId(),
                $data->getServiceName()
            );

            $customer = false;

            if ($user->getId()) {
                $customer = $user->getCustomerBySocial();
            } elseif ($this->_customerSession->getSocialAction() == 'register') {
                try {
                    $customer = $user->createCustomer($data);

                    $this->_eventManager->dispatch(
                        'customer_register_success',
                        ['account_controller' => $this, 'customer' => $customer]
                    );
                } catch (\Exception $e) {
                    $this->addMessage(__($e->getMessage()));
                    return $this->_redirect('*/register');
                }
            }

            if ($customer && $customer->getId()) {
                if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                }

                $this->_customerSession->regenerateId();
                $this->_customerSession->setCustomerAsLoggedIn($customer);
            } else {
                $this->addMessage(__('No associated account could be found.'));
                return $this->_redirect('*/register');
            }
        } else {
            $this->addMessage(__('An error occurred while logging you in.'));
            return $this->_redirect('customer/account/login');
        }

        if ($url = $this->_customerSession->getRedirectUrl()) {
            $this->_customerSession->unsRedirectUrl();
            return $this->_redirect($url);
        }

        return $this->_redirect('customer/account');
    }

    /**
     * Connect social account
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function connect()
    {
        // Redirect if not logged in
        if (!$this->_customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account');
        }

        $name = $this->service->service();
        $data = new \Magento\Framework\DataObject(
            $this->_customerSession->getSocialData()
        );

        $customer = $this->_customerFactory->create()->load(
            $data->getUserId(),
            'user_id'
        );

        // Check if account is already being used
        if (!$customer->getId()) {
            $customer = $this->_customerFactory->create();

            try {
                $customer->setData([
                    'customer_id' => $this->_customerSession->getCustomer()->getId(),
                    'user_id' => $data->getUserId(),
                    'type' => $this->getServiceName(),
                ])->save();

                $this->addMessage(
                    sprintf(__('You have successfully connected your %s account.'), $name),
                    MessageInterface::TYPE_SUCCESS
                );
            } catch (\Exception $e) {
                $customer->unsData()->delete();
                $this->addMessage(
                    sprintf(__('An error occurred while connecting your %s account.'), $name)
                );
            }
        } else {
            $this->addMessage(
                sprintf(__('The requested %s account is already in use.'), $name)
            );
        }

        return $this->_redirect('*/accounts');
    }

    /**
     * Disconnect social account
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function disconnect()
    {
        // Redirect if not logged in
        if (!$this->_customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account');
        }

        $name = $this->service->service();
        $data = new \Magento\Framework\DataObject(
            $this->_customerSession->getSocialData()
        );

        // Delete customer entry
        try {
            $this->_customerFactory->create()->loadByCustomerIdType(
                $this->_customerSession->getCustomer()->getId(),
                $this->getServiceName()
            )->delete();

            $this->addMessage(
                sprintf(__('You have successfully disconnected your %s account.'), $name),
                MessageInterface::TYPE_SUCCESS
            );
        } catch (\Exception $e) {
            $this->addMessage(
                sprintf(__('An error occurred while disconnecting your %s account.'), $name)
            );
        }

        return $this->_redirect('*/accounts');
    }

    /**
     * Authorize service
     */
    public function execute()
    {
        if ($this->service = $this->_service->getService($this->getServiceName())) {
            // Set action
            if ($action = $this->getRequest()->getParam('action')) {
                $this->_customerSession->setSocialAction($action);
            }

            // Connect or disconnect account
            if ($this->_customerSession->getSocialAction() == 'disconnect') return $this->disconnect();

            if ($this->getRequest()->getParam('continue')) {
                return $this->_auth(
                    new \Magento\Framework\DataObject($this->_customerSession->getSocialData())
                );
            }

            // Redirect URL (path)
            if ($url = $this->getRequest()->getParam('redirect_url')) {
                $this->_customerSession->setRedirectUrl($this->getPathFromUrl($url));
            }

            return $this->auth();
        }
    }
}
