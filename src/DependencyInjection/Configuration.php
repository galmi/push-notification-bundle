<?php

namespace Dmytrof\PushNotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dmytrof_push_notification');

        $rootNode
            ->children()
                ->scalarNode('provider')->isRequired()->end()
                ->arrayNode('one_signal')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('app_id')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('app_auth_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('auto_register')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('subdomain')
                            ->defaultFalse()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('safari_web_id')
                            ->defaultFalse()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('notify_button')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('web_sdk_init_template')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('web_sdk_tags_template')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
