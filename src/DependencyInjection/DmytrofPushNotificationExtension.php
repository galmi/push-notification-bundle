<?php

namespace Dmytrof\PushNotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;


class DmytrofPushNotificationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('twig.yml');

        switch ($config['provider']) {
            case 'one_signal':
                $this->configureOneSignal($config['one_signal'], $container);
                break;
            default:
                throw new InvalidConfigurationException('The child node "provider" at path "'.$this->getAlias().'" must be "one_signal"');
        }

        $container->getDefinition($this->getAlias().'.config_parameter.twig_extension')->addMethodCall('setConfigPrefix', [$this->getAlias()]);
    }

    /**
     * Configure the OneSignal manager
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function configureOneSignal(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            throw new InvalidConfigurationException('The configuration "one_signal" at path "'.$this->getAlias().'" must be enabled.');
        }
        foreach (array_diff_key($config, ['enabled' => true]) as $key => $value)
        {
            $container->setParameter($this->getAlias().'.one_signal.'.$key, $value);
        }
        $container->setParameter($this->getAlias().'.provider.code', 'one_signal');
        $container->setAlias($this->getAlias().'.provider', $this->getAlias().'.provider.one_signal');
    }
}
