<?php

namespace LoganStellway\Social\Controller\Auth;

/**
 * Authorization Controller
 */
class Facebook extends AbstractAuth
{
    /**
     * Authorize
     */
    public function auth()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            $token = $this->service->requestAccessToken(
                $code,
                $this->getRequest()->getParam('state') // CSRF state
            );

            // Fetch profile info
            $data = new \Magento\Framework\DataObject(
                json_decode($this->service->request('/me'), true)
            );
            $data->setServiceName($this->getServiceName());
            $data->setUserId($data->getId());

            return $this->_auth($data);
        } elseif($error = $this->getRequest()->getParam('error')) {
            $this->addError(__('We could not log you in.'));
            return $this->_redirect('customer/account');
        } else {
            return $this->redirect($this->service->getAuthorizationUri(), true);
        }
    }
}
