<?php

namespace Dmytrof\PushNotificationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Dmytrof\PushNotificationBundle\Provider\ProviderInterface;
use Dmytrof\PushNotificationBundle\Exception\RuntimeException;

class WebPushSDKExtension extends \Twig_Extension
{
    protected $container;
    protected $provider;
    protected $configPrefix;
    protected $sdkRendered;

    /**
     * @param ContainerInterface $container
     *
     * @return WebPushSDKExtension
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
     * @param ProviderInterface $provider
     *
     * @return WebPushSDKExtension
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
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
     * @return WebPushSDKExtension
     */
    public function setConfigPrefix($configPrefix)
    {
        $this->configPrefix = $configPrefix;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSDKRendered()
    {
        return $this->sdkRendered;
    }

    /**
     * @param boolean $rendered
     *
     * @return WebPushSDKExtension
     */
    public function setSDKRendered($rendered=true)
    {
        $this->sdkRendered = $rendered;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('dmytrof_push_notification_parameter', [$this, 'getPushNotificationParameter']),
            new \Twig_SimpleFunction('dmytrof_push_notification_provider_parameter', [$this, 'getPushNotificationProviderParameter']),
            new \Twig_SimpleFunction('dmytrof_push_notification_web_sdk', [$this, 'renderSDK'], [
               'needs_environment' => true,
               'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('dmytrof_push_notification_tags', [$this, 'renderTags'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ])
        ];
    }
    
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getPushNotificationParameter($name)
    {
        if (substr($name, 0, strlen($this->getConfigPrefix())) != $this->getConfigPrefix()) {
            $name = $this->getConfigPrefix().'.'.$name;
        }
        return $this->getContainer()->getParameter($name);
    }
    
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getPushNotificationProviderParameter($name)
    {
        if (substr($name, 0, strlen($this->getProvider()->getCode())) != $this->getProvider()->getCode() && substr($name, 0, strlen($this->getConfigPrefix())) != $this->getConfigPrefix()) {
            $name = $this->getProvider()->getCode().'.'.$name;
        }
        return $this->getPushNotificationParameter($name);
    }

    /**
     * @param \Twig_Environment $environment
     *
     * @return string
     */
    public function renderSDK(\Twig_Environment $environment)
    {
        if ($this->isSDKRendered()) {
            throw new RuntimeException('Web SDK is already rendered');
        }
        $this->setSDKRendered();
        $template = $this->getPushNotificationProviderParameter('web_sdk_init_template');
        return $environment->render($template ?: 'DmytrofPushNotificationBundle:'.$this->getProvider()->getCode().':web_sdk/init.html.twig');
    }

    /**
     * @param \Twig_Environment $environment
     * @param boolean $wrapScript
     *
     * @return string
     */
    public function renderTags(\Twig_Environment $environment, $wrapScript=false)
    {
        $template = $this->getPushNotificationProviderParameter('web_sdk_tags_template');
        return $environment->render($template ?: 'DmytrofPushNotificationBundle:'.$this->getProvider()->getCode().':web_sdk/tags.html.twig', [
            'wrapScript'    => $wrapScript,
            'tags'          => $this->getProvider()->getTags(true),
            'removableTags' => $this->getProvider()->getRemovableTags(true),
        ]);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dmytrof_push_notification_web_push_sdk_extension';
    }
}