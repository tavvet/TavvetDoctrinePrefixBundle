<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\Doctrine\ORM\Mapping;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use Tavvet\DoctrinePrefixBundle\Doctrine\ORM\Mapping\PrefixNamingStrategy;

class PrefixNamingStrategyTest extends TestCase
{
    private UnderscoreNamingStrategy $base;
    private PrefixNamingStrategy $strategy;

    protected function setUp(): void
    {
        $this->base = new UnderscoreNamingStrategy();
        $this->strategy = $this->createStrategy(UnderscoreNamingStrategy::class, [], 't_', 'c_');
    }

    public function testClassToTableNameAddsTablePrefix(): void
    {
        self::assertSame(
            't_' . $this->base->classToTableName('App\\Entity\\UserAccount'),
            $this->strategy->classToTableName('App\\Entity\\UserAccount')
        );
    }

    public function testPropertyToColumnNameAddsColumnPrefix(): void
    {
        self::assertSame(
            'c_' . $this->base->propertyToColumnName('firstName', 'App\\Entity\\User'),
            $this->strategy->propertyToColumnName('firstName', 'App\\Entity\\User')
        );
    }

    public function testEmbeddedFieldToColumnNameAddsColumnPrefix(): void
    {
        self::assertSame(
            'c_' . $this->base->embeddedFieldToColumnName('address', 'city', 'App\\Entity\\User', 'App\\Entity\\Address'),
            $this->strategy->embeddedFieldToColumnName('address', 'city', 'App\\Entity\\User', 'App\\Entity\\Address')
        );
    }

    public function testReferenceColumnNameAddsColumnPrefix(): void
    {
        self::assertSame(
            'c_' . $this->base->referenceColumnName(),
            $this->strategy->referenceColumnName()
        );
    }

    public function testJoinColumnNameForwardsClassNameAndAddsColumnPrefix(): void
    {
        // Regression test: joinColumnName() used to call the wrapped strategy with
        // only $propertyName, but NamingStrategy::joinColumnName() requires $className
        // too - that used to throw \ArgumentCountError for every real base strategy.
        self::assertSame(
            'c_' . $this->base->joinColumnName('author', 'App\\Entity\\Article'),
            $this->strategy->joinColumnName('author', 'App\\Entity\\Article')
        );
    }

    public function testJoinTableNameAddsTablePrefix(): void
    {
        self::assertSame(
            't_' . $this->base->joinTableName('App\\Entity\\User', 'App\\Entity\\Group', 'groups'),
            $this->strategy->joinTableName('App\\Entity\\User', 'App\\Entity\\Group', 'groups')
        );
    }

    public function testJoinKeyColumnNameAddsColumnPrefix(): void
    {
        self::assertSame(
            'c_' . $this->base->joinKeyColumnName('App\\Entity\\User', null),
            $this->strategy->joinKeyColumnName('App\\Entity\\User', null)
        );
    }

    public function testConstructorArgumentsAreForwardedToBaseStrategy(): void
    {
        $upper = new UnderscoreNamingStrategy(\CASE_UPPER);
        $strategy = $this->createStrategy(UnderscoreNamingStrategy::class, [\CASE_UPPER], 't_', '');

        self::assertSame(
            't_' . $upper->classToTableName('App\\Entity\\UserAccount'),
            $strategy->classToTableName('App\\Entity\\UserAccount')
        );
    }

    public function testEmptyPrefixesAreNoOp(): void
    {
        $strategy = $this->createStrategy(UnderscoreNamingStrategy::class, [], '', '');

        self::assertSame($this->base->classToTableName('User'), $strategy->classToTableName('User'));
        self::assertSame($this->base->propertyToColumnName('name', 'User'), $strategy->propertyToColumnName('name', 'User'));
    }

    public function testUsingStrategyBeforeSetStrategyThrowsClearError(): void
    {
        $strategy = new PrefixNamingStrategy('t_', 'c_');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('setStrategy() must be called');

        $strategy->classToTableName('User');
    }

    /**
     * @param array<int, mixed> $arguments constructor arguments for the wrapped base strategy
     */
    private function createStrategy(
        string $wrappedStrategyClass,
        array $arguments,
        string $tablePrefix,
        string $columnPrefix,
    ): PrefixNamingStrategy {
        $strategy = new PrefixNamingStrategy($tablePrefix, $columnPrefix);
        $strategy->setStrategy($wrappedStrategyClass, $arguments);

        return $strategy;
    }
}
