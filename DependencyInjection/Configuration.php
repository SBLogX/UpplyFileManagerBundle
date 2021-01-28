<?php

namespace Upply\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('upply_file_manager');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('adapter')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('azure_blob_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('azure_container_name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('directories')
                    ->useAttributeAsKey('_name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
