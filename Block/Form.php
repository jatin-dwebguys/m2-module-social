<?php

namespace LoganStellway\Social\Block;

/**
 * Dependencies
 */
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use LoganStellway\Social\Helper\Config;
use LoganStellway\Social\Model\Session;

/**
 * Register Block
 */
class Form extends Template
{
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
        Context $context,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
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
}
