<?php

namespace LoganStellway\Social\Controller\Auth;

/**
 * Authorization Controller
 */
class Index extends AbstractAuth
{
    /**
     * Authorize
     */
    public function auth()
    {
        return $this->_redirect('*/*/' . $this->getServiceName());
    }
}
