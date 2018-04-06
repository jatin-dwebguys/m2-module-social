<?php

namespace LoganStellway\Social\Plugin\Customer;

/**
 * Onepage Checkout Plugin
 */
class AuthenticationPopupPlugin
{
    /**
     * @param \LoganStellway\Social\Helper\Config $config [description]
     */
    public function __construct(
        \LoganStellway\Social\Helper\Config $config
    ) {
        $this->_config = $config;
    }

    /**
     * Add login methods to checkout
     * 
     * @param  \Magento\Checkout\Block\Onepage $subject
     * @param  string                          $result
     * @return string
     */
    public function afterGetJsLayout(\Magento\Customer\Block\Account\AuthenticationPopup $subject, string $result)
    {
        $result = json_decode($result, true);
        $methods = [];

        foreach (['facebook', 'google'] as $service) {
            if ($this->_config->getServiceAvailable($service)) {
                $methods[] = [
                    'name' => ucwords($service),
                    'login' => $this->_config->getServiceLoginUrl($service),
                    'register' => $this->_config->getServiceRegisterUrl($service)
                ];
            }
        }

        // Add login buttons to shipping address area
        if (isset($result['components']['authenticationPopup']['children']['additional-login-form-fields']['children']['social-login-options']['config'])) {
            $result['components']['authenticationPopup']['children']['additional-login-form-fields']['children']['social-login-options']['config']['methods'] = $methods;
        }

        // Add register buttons to shipping address area
        if (isset($result['components']['authenticationPopup']['children']['additional-register-form-fields']['children']['social-login-options']['config'])) {
            $result['components']['authenticationPopup']['children']['additional-register-form-fields']['children']['social-login-options']['config']['methods'] = $methods;
        }

        return json_encode($result);
    }
}
