<?php

namespace Netosoft\DomainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netosoft_domain');

        $rootNode
            ->children()
                ->arrayNode('entity')
                    ->isRequired()
                    ->children()
                        ->scalarNode('command_log')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('repository')
                    ->isRequired()
                    ->children()
                        ->scalarNode('command_log')->isRequired()->end()
                    ->end()
                ->end()
                ->scalarNode('default_command_logger')->defaultValue('netosoft_domain.original_default_command_logger')->end()
                ->scalarNode('cache_logger_utils')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}
