<?php

namespace LoganStellway\Social\Block;

/**
 * Dependencies
 */
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use LoganStellway\Social\Helper\Config;
use LoganStellway\Social\Model\Session;
use LoganStellway\Social\Model\Customer as SocialCustomer;

/**
 * Register Block
 */
class Customer extends Template
{
    /**
     * @var array
     */
    protected $_accounts = [];

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Config  $config
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Config $config,
        Session $customerSession,
        SocialCustomer $socialCustomer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_config = $config;
        $this->_customerSession = $customerSession;
        $this->_socialCustomer = $socialCustomer;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get customer session
     * @return Session
     */
    public function getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Social account is connected
     * @param  string  $service
     * @return boolean
     */
    public function isConnected(string $service)
    {
        if (!isset($this->_accounts[$service])) {
            $customer = $this->_socialCustomer->loadByCustomerIdType(
                $this->_customerSession->getCustomer()->getId(),
                $service
            );

            $this->_accounts[$service] = $customer->getId() ? true : false;
        }
        return $this->_accounts[$service];
    }
}
