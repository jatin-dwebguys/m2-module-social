<?php

namespace LoganStellway\Social\Plugin\Customer;

/**
 * Logout success
 */
class LogoutSuccessPlugin
{
    /**
     * @param \LoganStellway\Social\Model\Session $customerSession [description]
     */
    public function __construct(
        \LoganStellway\Social\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * @param  \Magento\Customer\Controller\Account\LogoutSuccess $subject
     * @param  callable $proceed
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\LogoutSuccess $subject, callable $proceed)
    {
        $this->_customerSession->clearCustomerData();
        return $proceed();
    }
}
