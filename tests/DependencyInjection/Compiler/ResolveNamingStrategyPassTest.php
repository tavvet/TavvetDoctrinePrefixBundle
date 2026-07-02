<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\DependencyInjection\Compiler;

use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Tavvet\DoctrinePrefixBundle\DependencyInjection\Compiler\ResolveNamingStrategyPass;

class ResolveNamingStrategyPassTest extends TestCase
{
    public function testResolvesConfiguredServiceIdToItsClass(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('tavvet_doctrine_prefix.naming_strategy.type', 'app.naming_strategy');
        $container->register('app.naming_strategy', DefaultNamingStrategy::class);

        (new ResolveNamingStrategyPass())->process($container);

        self::assertSame(
            DefaultNamingStrategy::class,
            $container->getParameter('tavvet_doctrine_prefix.naming_strategy.type')
        );
    }

    public function testResolvesThroughAnAlias(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('tavvet_doctrine_prefix.naming_strategy.type', 'app.aliased_naming_strategy');
        $container->register('app.real_naming_strategy', DefaultNamingStrategy::class);
        $container->setAlias('app.aliased_naming_strategy', 'app.real_naming_strategy');

        (new ResolveNamingStrategyPass())->process($container);

        self::assertSame(
            DefaultNamingStrategy::class,
            $container->getParameter('tavvet_doctrine_prefix.naming_strategy.type')
        );
    }

    public function testFailsFastWhenConfiguredServiceIdDoesNotExist(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('tavvet_doctrine_prefix.naming_strategy.type', 'does_not_exist');

        $this->expectException(ServiceNotFoundException::class);

        (new ResolveNamingStrategyPass())->process($container);
    }
}
