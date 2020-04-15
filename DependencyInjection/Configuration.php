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
                ->scalarNode('blob_storage_url')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('storage_dirnames')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('client')->isRequired()->cannotBeEmpty()->defaultValue('client-files')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
