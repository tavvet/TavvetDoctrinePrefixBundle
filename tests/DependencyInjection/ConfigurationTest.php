<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Tavvet\DoctrinePrefixBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = $this->process([]);

        self::assertSame('', $config['table_prefix']);
        self::assertSame('', $config['column_prefix']);
        self::assertSame('doctrine.orm.naming_strategy.underscore', $config['naming_strategy']['type']);
        self::assertSame([], $config['naming_strategy']['arguments']);
    }

    public function testCustomValues(): void
    {
        $config = $this->process([[
            'table_prefix' => 't_',
            'column_prefix' => 'c__',
            'naming_strategy' => [
                'type' => 'doctrine.orm.naming_strategy.default',
                'arguments' => ['foo', 'bar'],
            ],
        ]]);

        self::assertSame('t_', $config['table_prefix']);
        self::assertSame('c__', $config['column_prefix']);
        self::assertSame('doctrine.orm.naming_strategy.default', $config['naming_strategy']['type']);
        self::assertSame(['foo', 'bar'], $config['naming_strategy']['arguments']);
    }

    /**
     * @param array<int, array<string, mixed>> $configs
     *
     * @return array<string, mixed>
     */
    private function process(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }
}
