<?php

namespace Markup\FallbackPasswordEncoderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('markup_fallback_password_encoder');

        $rootNode
            ->children()
                ->arrayNode('encoders')
                    ->children()
                        ->arrayNode('primary')
                        ->cannotBeEmpty()
                            ->children()
                                ->scalarNode('id')->end()
                            ->end()
                        ->end()
                        ->arrayNode('fallbacks')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('id')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('manipulators')
                    ->defaultValue(array())
                    ->prototype('variable')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
