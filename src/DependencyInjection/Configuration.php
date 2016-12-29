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
                        ->scalarNode('app_id')->isRequired()->end()
                        ->scalarNode('app_auth_key')->isRequired()->end()
                        ->scalarNode('subdomain')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
