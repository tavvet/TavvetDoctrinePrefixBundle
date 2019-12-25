<?php

namespace Tavvet\DoctrinePrefixBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tavvet_doctrine_prefix');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('table_prefix')->defaultValue('')->end()
                ->scalarNode('column_prefix')->defaultValue('')->end()
                ->scalarNode('naming_strategy')->defaultValue('doctrine.orm.naming_strategy.default')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
