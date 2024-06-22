<?php

namespace Tavvet\DoctrinePrefixBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tavvet_doctrine_prefix');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('table_prefix')->defaultValue('')->end()
                ->scalarNode('column_prefix')->defaultValue('')->end()
                ->arrayNode('naming_strategy')
                    ->children()
                        ->scalarNode('type')->defaultValue('doctrine.orm.naming_strategy.underscore')->end()
                        ->arrayNode('arguments')
                            ->scalarPrototype()->defaultValue([])->end()
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
