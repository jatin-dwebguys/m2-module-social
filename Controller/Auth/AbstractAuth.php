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
use LoganStellway\Social\Model\Customer;

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
     * @var Customer
     */
    protected $_customer;

    /**
     * Error messages
     * @var array
     */
    protected $errors = [
        'login' => [
            'general' => 'An error occurred while logging you in.',
            'no_customer' => 'No associated account could be found.'
        ],
        'register' => [
            'general' => 'An error occurred while creating your account.',
            'no_customer' => 'An error occurred while creating your account.'
        ],
    ];

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
        Customer $customer
    ) {
        parent::__construct($context);

        $this->_resultFactory = $resultFactory;
        $this->_config = $config;
        $this->_service = $service;
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
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
     * Get Error message
     * @param  string $key
     * @return string|null
     */
    protected function addError($message = '', $key = false)
    {
        if ($key) {
            $action = $this->_customerSession->getSocialAction();

            if (isset($this->errors[$action]) && isset($this->errors[$action][$key])) {
                $message = __($this->errors[$action][$key]);
            }
        }

        if ($message) {
            $this->messageManager->addUniqueMessages([
                $this->messageManager->createMessage(
                    MessageInterface::TYPE_ERROR
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
            $user = $this->_customer->loadByIdType(
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
                    $this->addError(__($e->getMessage()));
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
                $this->addError(null, 'no_customer');
                return $this->_redirect('*/register');
            }
        } else {
            $this->addError(null, 'general');
            return $this->_redirect('customer/account/login');
        }

        if ($url = $this->_customerSession->getRedirectUrl()) {
            $this->_customerSession->unsRedirectUrl();
            return $this->_redirect($url);
        }

        return $this->_redirect('customer/account');
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
