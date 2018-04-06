<?php

namespace LoganStellway\Social\Helper;

/**
 * Service Helper
 */
class Service extends \OAuth\ServiceFactory
{
    /**
     * Gets the fully qualified name of the service
     *
     * @param string $serviceName The name of the service of which to get the fully qualified name
     * @param string $type        The type of the service to get (either OAuth1 or OAuth2)
     *
     * @return string The fully qualified name of the service
     */
    protected function getFullyQualifiedServiceName($serviceName, $type)
    {
        $serviceName = ucfirst($serviceName);

        if (isset($this->serviceClassMap[$type][$serviceName])) {
            return $this->serviceClassMap[$type][$serviceName];
        }

        return '\\OAuth\\' . $type . '\\Service\\' . $serviceName;
    }

    /**
     * Returns service class name
     * @param string $serviceName
     * @return string
     */
    public function getServiceClass($serviceName) {
        foreach ($this->serviceBuilders as $version => $buildMethod) {
            $fullyQualifiedServiceName = $this->getFullyQualifiedServiceName($serviceName, $version);

            if (class_exists($fullyQualifiedServiceName)) {
                return $fullyQualifiedServiceName;
            }
        }
        return null;
    }
}
