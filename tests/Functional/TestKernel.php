<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Tavvet\DoctrinePrefixBundle\TavvetDoctrinePrefixBundle;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new TavvetDoctrinePrefixBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
        ]);

        $orm = [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'tavvet_doctrine_prefix.prefix_naming_strategy',
            'auto_mapping' => false,
            'mappings' => [
                'Test' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => __DIR__ . '/Fixtures/Entity',
                    'prefix' => 'Tavvet\DoctrinePrefixBundle\Tests\Functional\Fixtures\Entity',
                ],
            ],
        ];

        // On PHP 8.4 the "highest" dependency set resolves symfony/var-exporter to
        // v8, which dropped the LazyGhost helper Doctrine ORM 3 relies on; the ORM
        // then requires PHP native lazy objects, which must be opted into (and only
        // exist on 8.4+). A real 8.4 app on this stack has to do the same.
        if (\PHP_VERSION_ID >= 80400) {
            $orm['enable_native_lazy_objects'] = true;
        }

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => $orm,
        ]);

        $container->extension('tavvet_doctrine_prefix', [
            'table_prefix' => 't_',
            'column_prefix' => 'c_',
        ]);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/tavvet-doctrine-prefix-tests/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/tavvet-doctrine-prefix-tests/log';
    }
}
