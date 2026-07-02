<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\DependencyInjection;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tavvet\DoctrinePrefixBundle\DependencyInjection\TavvetDoctrinePrefixExtension;
use Tavvet\DoctrinePrefixBundle\Doctrine\ORM\Mapping\PrefixNamingStrategy;
use Tavvet\DoctrinePrefixBundle\TavvetDoctrinePrefixBundle;

class TavvetDoctrinePrefixExtensionTest extends TestCase
{
    public function testDefaultParametersAreExposed(): void
    {
        $container = new ContainerBuilder();
        (new TavvetDoctrinePrefixExtension())->load([], $container);

        self::assertSame('', $container->getParameter('tavvet_doctrine_prefix.table_prefix'));
        self::assertSame('', $container->getParameter('tavvet_doctrine_prefix.column_prefix'));
        self::assertSame(
            'doctrine.orm.naming_strategy.underscore',
            $container->getParameter('tavvet_doctrine_prefix.naming_strategy.type')
        );
        self::assertSame([], $container->getParameter('tavvet_doctrine_prefix.naming_strategy.arguments'));
    }

    public function testCustomConfigOverridesParameters(): void
    {
        $container = new ContainerBuilder();
        (new TavvetDoctrinePrefixExtension())->load([[
            'table_prefix' => 't_',
            'column_prefix' => 'c__',
            'naming_strategy' => ['type' => 'doctrine.orm.naming_strategy.default', 'arguments' => ['x']],
        ]], $container);

        self::assertSame('t_', $container->getParameter('tavvet_doctrine_prefix.table_prefix'));
        self::assertSame('c__', $container->getParameter('tavvet_doctrine_prefix.column_prefix'));
        self::assertSame(
            'doctrine.orm.naming_strategy.default',
            $container->getParameter('tavvet_doctrine_prefix.naming_strategy.type')
        );
        self::assertSame(['x'], $container->getParameter('tavvet_doctrine_prefix.naming_strategy.arguments'));
    }

    public function testServiceDefinitionIsWiredCorrectly(): void
    {
        $container = new ContainerBuilder();
        (new TavvetDoctrinePrefixExtension())->load([], $container);

        self::assertTrue($container->hasDefinition('tavvet_doctrine_prefix.prefix_naming_strategy'));

        $definition = $container->getDefinition('tavvet_doctrine_prefix.prefix_naming_strategy');

        self::assertSame(PrefixNamingStrategy::class, $definition->getClass());
        self::assertSame(
            ['%tavvet_doctrine_prefix.table_prefix%', '%tavvet_doctrine_prefix.column_prefix%'],
            $definition->getArguments()
        );

        $methodCalls = $definition->getMethodCalls();
        self::assertSame('setStrategy', $methodCalls[0][0]);
        self::assertSame(
            ['%tavvet_doctrine_prefix.naming_strategy.type%', '%tavvet_doctrine_prefix.naming_strategy.arguments%'],
            $methodCalls[0][1]
        );
    }

    public function testServiceResolvesToWorkingNamingStrategyWhenBaseStrategyIsAvailable(): void
    {
        $container = new ContainerBuilder();
        (new TavvetDoctrinePrefixBundle())->build($container);
        (new TavvetDoctrinePrefixExtension())->load([[
            'table_prefix' => 't_',
            'column_prefix' => 'c_',
        ]], $container);

        // In a real app this service is registered by doctrine/doctrine-bundle.
        $container->register('doctrine.orm.naming_strategy.underscore', UnderscoreNamingStrategy::class);
        $container->getDefinition('tavvet_doctrine_prefix.prefix_naming_strategy')->setPublic(true);
        $container->compile();

        $strategy = $container->get('tavvet_doctrine_prefix.prefix_naming_strategy');

        self::assertInstanceOf(PrefixNamingStrategy::class, $strategy);
        self::assertSame('t_user', $strategy->classToTableName('User'));
        self::assertSame('c_name', $strategy->propertyToColumnName('name', 'User'));
    }
}
