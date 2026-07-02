<?php

namespace Tavvet\DoctrinePrefixBundle\Doctrine\ORM\Mapping;

use Doctrine\ORM\Mapping\NamingStrategy;

class PrefixNamingStrategy implements NamingStrategy
{
    protected ?NamingStrategy $strategy = null;

    public function __construct(
        protected readonly string $tablePrefix,
        protected readonly string $columnPrefix,
    ) {
    }

    /**
     * @param class-string<NamingStrategy> $strategyClass resolved at container-compile
     *        time by ResolveNamingStrategyPass from the configured service id
     * @param list<mixed> $arguments constructor arguments for the base strategy
     */
    public function setStrategy(string $strategyClass, array $arguments = []): void
    {
        $this->strategy = new $strategyClass(...$arguments);
    }

    public function classToTableName(string $className): string
    {
        return $this->tablePrefix . $this->strategy()->classToTableName($className);
    }

    public function propertyToColumnName(string $propertyName, string $className): string
    {
        return $this->columnPrefix . $this->strategy()->propertyToColumnName($propertyName, $className);
    }

    public function embeddedFieldToColumnName(
        string $propertyName,
        string $embeddedColumnName,
        string $className,
        string $embeddedClassName,
    ): string {
        return $this->columnPrefix
            . $this->strategy()->embeddedFieldToColumnName(
                $propertyName,
                $embeddedColumnName,
                $className,
                $embeddedClassName
            )
        ;
    }

    public function referenceColumnName(): string
    {
        return $this->columnPrefix . $this->strategy()->referenceColumnName();
    }

    public function joinColumnName(string $propertyName, string $className): string
    {
        return $this->columnPrefix . $this->strategy()->joinColumnName($propertyName, $className);
    }

    public function joinTableName(string $sourceEntity, string $targetEntity, string $propertyName): string
    {
        return $this->tablePrefix . $this->strategy()->joinTableName($sourceEntity, $targetEntity, $propertyName);
    }

    public function joinKeyColumnName(string $entityName, ?string $referencedColumnName = null): string
    {
        return $this->columnPrefix . $this->strategy()->joinKeyColumnName($entityName, $referencedColumnName);
    }

    private function strategy(): NamingStrategy
    {
        return $this->strategy ?? throw new \LogicException(sprintf(
            '%s::setStrategy() must be called before the naming strategy is used.',
            self::class,
        ));
    }
}
