<?php

namespace Tavvet\DoctrinePrefixBundle\Doctrine\ORM\Mapping;

use Doctrine\ORM\Mapping\NamingStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrefixNamingStrategy implements NamingStrategy
{
    private ContainerInterface $container;

    protected ?NamingStrategy $strategy;

    public function __construct(
        protected readonly string $tablePrefix,
        protected readonly string $columnPrefix,
    ) {
        $this->strategy = null;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setStrategy(string $strategyType, array $arguments = []): void
    {
        $strategyClass = $this->container->getParameter($strategyType.'.class');
        $this->strategy = new $strategyClass(...$arguments);
    }

    public function classToTableName(string $className): string
    {
        return $this->tablePrefix . $this->strategy->classToTableName($className);
    }

    public function propertyToColumnName(string $propertyName, ?string $className = null): string
    {
        return $this->columnPrefix . $this->strategy->propertyToColumnName($propertyName, $className);
    }

    public function embeddedFieldToColumnName(
        string $propertyName,
        string $embeddedColumnName,
        ?string $className = null,
        ?string $embeddedClassName = null,
    ): string {
        return $this->columnPrefix
            . $this->strategy->embeddedFieldToColumnName(
                $propertyName,
                $embeddedColumnName,
                $className,
                $embeddedClassName
            )
        ;
    }

    public function referenceColumnName(): string
    {
        return $this->columnPrefix . $this->strategy->referenceColumnName();
    }

    public function joinColumnName(string $propertyName, ?string $className = null): string
    {
        return $this->columnPrefix . $this->strategy->joinColumnName($propertyName);
    }

    public function joinTableName(string $sourceEntity, string $targetEntity, ?string $propertyName = null): string
    {
        return $this->tablePrefix . $this->strategy->joinTableName($sourceEntity, $targetEntity, $propertyName);
    }

    public function joinKeyColumnName(string $entityName, ?string $referencedColumnName = null): string
    {
        return $this->columnPrefix . $this->strategy->joinKeyColumnName($entityName, $referencedColumnName);
    }
}
