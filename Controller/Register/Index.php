<?php

namespace LoganStellway\Social\Controller\Register;

/**
 * Dependencies
 */
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Registration;
use Magento\Framework\Data\Form\FormKey\Validator;
use LoganStellway\Social\Model\Session;

/**
 * Registration
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Registration
     */
    protected $_registration;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Registration $registration
     * @param Validator    $formKeyValidator
     * @param Session      $customerSession
     * @param Context      $context
     */
    public function __construct(
        PageFactory $pageFactory,
        Registration $registration,
        Validator $formKeyValidator,
        Session $customerSession,
        Context $context
    ) {
        parent::__construct($context);

        $this->_pageFactory = $pageFactory;
        $this->_registration = $registration;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerSession = $customerSession;
    }

    public function execute()
    {
        $request = $this->getRequest();
        $data = $this->_customerSession->getSocialData();

        if (!$data || $this->_customerSession->isLoggedIn() || !$this->_registration->isAllowed()) {
            return $this->_redirect('customer/account');
        }

        if ($request->isPost() && $this->_formKeyValidator->validate($request)) {
            $data = new \Magento\Framework\DataObject($data);

            if ($var = $request->getParam('firstname')) $data->setFirstname($var);
            if ($var = $request->getParam('lastname')) $data->setLastname($var);
            if ($var = $request->getParam('email')) $data->setEmail($var);
            if ($var = $request->getParam('dob')) $data->setDob($var);

            $this->_customerSession->setSocialData($data->getData());

            return $this->_redirect('*/*/' . $data->getServiceName(), ['continue' => true]);
        }

        return $this->_pageFactory->create()->addHandle('social_register_index');
    }
}
