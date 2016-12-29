<?php

namespace Dmytrof\PushNotificationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Dmytrof\PushNotificationBundle\Provider\ProviderInterface;
use Dmytrof\PushNotificationBundle\Exception\RuntimeException;

class WebPushSDKExtension extends \Twig_Extension
{
    protected $container;
    protected $provider;
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
        return array(
            new \Twig_SimpleFunction('push_notification_web_sdk', array($this, 'renderSDK'), array(
               'needs_environment' => true,
               'is_safe' => array('html'),
            )),
            new \Twig_SimpleFunction('push_notification_tags', array($this, 'renderTags'), array(
                'needs_environment' => true,
                'is_safe' => array('html'),
            ))
        );
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
        return $environment->render('BikePushNotificationBundle:'.$this->getProvider()->getCode().':web_sdk/init.html.twig');
    }

    /**
     * @param \Twig_Environment $environment
     * @param boolean $wrapScript
     *
     * @return string
     */
    public function renderTags(\Twig_Environment $environment, $wrapScript=false)
    {
        return $environment->render('BikePushNotificationBundle:'.$this->getProvider()->getCode().':web_sdk/tags.html.twig', array(
            'wrapScript'    => $wrapScript,
            'tags'          => $this->getProvider()->getTags(true),
            'removableTags' => $this->getProvider()->getRemovableTags(true),
        ));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'push_notification_web_push_sdk_extension';
    }
}