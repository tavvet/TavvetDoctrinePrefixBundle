<?php

namespace Tavvet\DoctrinePrefixBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tavvet\DoctrinePrefixBundle\DependencyInjection\Compiler\ResolveNamingStrategyPass;

class TavvetDoctrinePrefixBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ResolveNamingStrategyPass());
    }
}
