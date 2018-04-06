<?php

namespace LoganStellway\Social\Controller;

/**
 * Dependencies
 */
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $_actionFactory;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Match social authorization request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $service = explode('/', $identifier);

        if (isset($service[2]) && $service[2] !== 'index' && in_array($service[1], ['login','register'])) {
            $request->setModuleName('social')->setControllerName('auth')->setActionName('index')->setParam(
                'service',
                $service[2]
            )->setParam(
                'action',
                $service[1]
            );

            return $this->_actionFactory->create(
                \Magento\Framework\App\Action\Forward::class,
                compact('request')
            );
        }
        return null;
    }
}
