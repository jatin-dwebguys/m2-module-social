<?php

namespace LoganStellway\Social\Plugin\Checkout\Block;

/**
 * Onepage Checkout Plugin
 */
class OnepagePlugin
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
    public function afterGetJsLayout(\Magento\Checkout\Block\Onepage $subject, string $result)
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

        // Add login buttons to authentication area
        if (isset($result['components']['checkout']['children']['authentication']['children']['additional-login-form-fields']['children']['social-login-options']['config'])) {
            $result['components']['checkout']['children']['authentication']['children']['additional-login-form-fields']['children']['social-login-options']['config']['methods'] = $methods;
        }

        // Add login buttons to shipping address area
        if (isset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['children']['additional-login-form-fields']['children']['social-login-options']['config'])) {
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['children']['additional-login-form-fields']['children']['social-login-options']['config']['methods'] = $methods;
        }

        // Add login buttons to payment area
        if (isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['customer-email']['children']['additional-login-form-fields']['children']['social-login-options']['config'])) {
            $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['customer-email']['children']['additional-login-form-fields']['children']['social-login-options']['config']['methods'] = $methods;
        }

        return json_encode($result);
    }
}
