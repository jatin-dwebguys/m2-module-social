<?php

namespace LoganStellway\Social\Controller\Accounts;

/**
 * Manage Social Accounts
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Customer\Model\Session            $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return $this->_redirect('/');
        }
        return $this->_pageFactory->create()->addHandle('social_accounts_index');
    }
}
