<?php

namespace LoganStellway\Social\Model;

/**
 * Service Class
 */
class Service
{
    /**
     * @var \OAuth\ServiceFactory
     */
    protected $_serviceFactory;

    /**
     * @var \OAuth\Common\Storage\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @var \OAuth\Common\Consumer\CredentialsFactory
     */
    protected $_credentialsFactory;

    /**
     * @var \LoganStellway\Social\Helper\Config
     */
    protected $_config;

    /**
     * @var \OAuth\OAuth2\Service\ServiceInterface
     */
    protected $_service;

    /**
     * Dependency Injection
     * @param \OAuth\ServiceFactory                     $serviceFactory
     * @param \OAuth\Common\Storage\SessionFactory      $sessionFactory
     * @param \OAuth\Common\Consumer\CredentialsFactory $credentialsFactory
     */
    public function __construct(
        \OAuth\ServiceFactory $serviceFactory,
        \OAuth\Common\Storage\SessionFactory $sessionFactory,
        \OAuth\Common\Consumer\CredentialsFactory $credentialsFactory,
        \LoganStellway\Social\Helper\Config $config
    ) {
        $this->_serviceFactory = $serviceFactory;
        $this->_sessionFactory = $sessionFactory;
        $this->_credentialsFactory = $credentialsFactory;
        $this->_config = $config;
    }

    /**
     * Get credentials object
     * @param  string $service
     * @return \OAuth\Common\Consumer\Credentials|false
     */
    protected function getCredentials(string $service)
    {
        if ($this->_config->getServiceAvailable($service)) {
            return $this->_credentialsFactory->create([
                'consumerId' => $this->_config->getServiceKey($service),
                'consumerSecret' => $this->_config->getServiceSecret($service),
                'callbackUrl' => $this->_config->getServiceAuthUrl($service)
            ]);
        }

        return false;
    }

    /**
     * Get service model
     * @param  string $service
     * @return \OAuth\OAuth2\Service\ServiceInterface
     */
    public function getService(string $service, string $baseApiUri = null, $apiVersion = '')
    {
        if ($this->_config->getServiceAvailable($service)) {
            return $this->_serviceFactory->createService(
                $service,
                $this->getCredentials($service),
                $this->_sessionFactory->create(),
                $this->_config->getServiceScopes($service),
                $baseApiUri,
                $apiVersion
            );
        }

        return $this;
    }
}
