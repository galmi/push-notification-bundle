<?php

namespace Dmytrof\PushNotificationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigParameterExtension extends \Twig_Extension
{
    protected $container;
    protected $configPrefix;

    /**
     * @param ContainerInterface $container
     *
     * @return ConfigParameterExtension
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return string
     */
    public function getConfigPrefix()
    {
        return $this->configPrefix;
    }

    /**
     * @param string $configPrefix
     *
     * @return ConfigParameterExtension
     */
    public function setConfigPrefix($configPrefix)
    {
        $this->configPrefix = $configPrefix;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('dmytrof_push_notification_parameter', function($name)
            {
                if (substr($name, 0, strlen($this->getConfigPrefix())) != $this->getConfigPrefix()) {
                    $name = $this->getConfigPrefix().'.'.$name;
                }
                return $this->getContainer()->getParameter($name);
            })
        );
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dmytrof_push_notification_config_parameter_extension';
    }
}