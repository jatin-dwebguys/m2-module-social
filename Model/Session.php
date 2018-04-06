<?php

namespace LoganStellway\Social\Model;

/**
 * Dependencies
 */
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Session Model
 */
class Session extends CustomerSession
{
    /**
     * Set social data
     * @param array $data
     */
    public function setSocialData(array $data)
    {
        $this->storage->setData('social_data', $data);
    }

    /**
     * Get social data
     * @return mixed
     */
    public function getSocialData()
    {
        $data = $this->storage->getData('social_data');
        return $data ?: [];
    }

    /**
     * Unset social data
     */
    public function unsSocialData()
    {
        $this->storage->unsetData('social_data');
    }

    /**
     * Set social action
     * @param string $action
     */
    public function setSocialAction(string $action)
    {
        $this->storage->setData('social_action', $action);
    }

    /**
     * Get social data
     * @return mixed
     */
    public function getSocialAction()
    {
        return $this->storage->getData('social_action');
    }

    /**
     * Unset social action
     */
    public function unsSocialAction()
    {
        $this->storage->unsetData('social_action');
    }

    /**
     * Set redirect URL
     * @param string $url
     */
    public function setRedirectUrl(string $url)
    {
        $this->storage->setData('social_redirect_url', $url);
    }

    /**
     * Get redirect URL
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->storage->getData('social_redirect_url');
    }

    /**
     * Unset redirect URL
     */
    public function unsRedirectUrl()
    {
        $this->storage->unsetData('social_redirect_url');
    }

    /**
     * Clear customer data
     */
    public function clearCustomerData()
    {
        $this->unsRedirectUrl();
        $this->unsSocialAction();
        $this->unsSocialData();
    }
}
