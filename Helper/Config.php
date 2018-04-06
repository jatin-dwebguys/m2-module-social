<?php

namespace LoganStellway\Social\Helper;

/**
 * Dependencies
 */
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Config Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PREFIX = 'loganstellway_social/';

    /**
     * Get config value
     * @param  string $field
     * @param  int.   $storeId
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get service config value
     * 
     * @param  string $service
     * @param  string $key
     * @return string
     */
    public function getServiceConfig(string $service, string $key)
    {
        return $this->getConfigValue(self::CONFIG_PREFIX . $service . '/' . $key);
    }

    /**
     * Get service auth URL
     * @param  string $service
     */
    public function getServiceAuthUrl(string $service)
    {
        return $this->_getUrl('social/auth/' . trim(strtolower($service)));
    }

    /**
     * Get service login URL
     * @param  string $service
     */
    public function getServiceLoginUrl(string $service)
    {
        return $this->_getUrl('social/login/' . trim(strtolower($service)));
    }

    /**
     * Get service register URL
     * @param  string $service
     */
    public function getServiceRegisterUrl(string $service)
    {
        return $this->_getUrl('social/register/' . trim(strtolower($service)));
    }

    /**
     * Check if service is available
     * @param  string $service
     * @return bool
     */
    public function getServiceAvailable(string $service)
    {
        return (bool) $this->getServiceEnabled($service) && $this->getServiceKey($service) && $this->getServiceSecret($service);
    }

    /**
     * Check if service is enabled
     * @param  string $service
     * @return bool
     */
    public function getServiceEnabled(string $service)
    {
        return (bool) $this->getServiceConfig($service, 'enabled');
    }

    /**
     * Return service key
     * @param  string $service
     * @return string
     */
    public function getServiceKey(string $service)
    {
        return $this->getServiceConfig($service, 'key');
    }

    /**
     * Return service secret
     * @param  string $service
     * @return string
     */
    public function getServiceSecret(string $service)
    {
        return $this->getServiceConfig($service, 'secret');
    }

    /**
     * Return service API version
     * @param  string $service
     * @return string
     */
    public function getServiceApiVersion(string $service)
    {
        return $this->getServiceConfig($service, 'api_version');
    }

    /**
     * Return service scopes
     * @param  string $service
     * @return mixed
     */
    public function getServiceScopes(string $service, bool $array = true)
    {
        $scopes = $this->getServiceConfig($service, 'scopes');
        if ($array) {
            $scopes = explode(',', $scopes);
        }
        return $scopes;
    }
}
