<?php

namespace LoganStellway\Social\Model\Config\Source\Service\Scope;

/**
 * Abstract options provider for service scopes
 */
abstract class AbstractScope implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \LoganStellway\Social\Helper\Service
     */
    protected $_serviceHelper;

    /**
     * Dependency Injection
     */
    public function __construct(
        \LoganStellway\Social\Helper\Service $serviceHelper
    ) {
        $this->_serviceHelper = $serviceHelper;
    }

    /**
     * Get service name
     */
    public function getServiceName()
    {
        $class = get_class($this);
        $class = explode('\\', $class);
        return strtolower(end($class));
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if ($service = $this->_serviceHelper->getServiceClass($this->getServiceName())) {
            $reflection = new \ReflectionClass($service);
            $scopes = [];

            foreach ($reflection->getConstants() as $constant => $value) {
                if (stripos($constant, 'scope_') === 0) {
                    $scopes[] = ['value' => $value, 'label' => $value];
                }
            }

            return $scopes;
        }
        return [];
    }
}
