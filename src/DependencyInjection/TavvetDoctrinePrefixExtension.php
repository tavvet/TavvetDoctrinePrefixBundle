<?php

namespace Tavvet\DoctrinePrefixBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TavvetDoctrinePrefixExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $processor = new Processor();

        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('tavvet_doctrine_prefix.table_prefix', $config['table_prefix']);
        $container->setParameter('tavvet_doctrine_prefix.column_prefix', $config['column_prefix']);
        $container->setParameter('tavvet_doctrine_prefix.naming_strategy', $config['naming_strategy']);
    }
}
