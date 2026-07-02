<?php

namespace Tavvet\DoctrinePrefixBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The base naming strategy is configured as a service id (e.g.
 * "doctrine.orm.naming_strategy.underscore"), but PrefixNamingStrategy needs
 * to build its own instance with custom constructor arguments rather than
 * reuse the shared service. A dumped/cached container has no way to look up
 * a service's class by id at runtime, so that resolution has to happen here,
 * at compile time, while service definitions are still introspectable.
 */
class ResolveNamingStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $strategyId = $container->getParameter('tavvet_doctrine_prefix.naming_strategy.type');
        \assert(\is_string($strategyId));

        $strategyClass = $container->findDefinition($strategyId)->getClass();
        if (null === $strategyClass) {
            throw new \LogicException(sprintf(
                'The naming strategy service "%s" configured for tavvet_doctrine_prefix has no class.',
                $strategyId,
            ));
        }

        $container->setParameter('tavvet_doctrine_prefix.naming_strategy.type', $strategyClass);
    }
}
