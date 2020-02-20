<?php

namespace Tavvet\DoctrinePrefixBundle\Doctrine\ORM\Mapping;

use Doctrine\ORM\Mapping\NamingStrategy;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PrefixNamingStrategy implements NamingStrategy
{
    use ContainerAwareTrait;
    
    /**
     * @var NamingStrategy
     */
    protected ?NamingStrategy $strategy;

    /**
     * @var string
     */
    protected string $tablePrefix;

    /**
     * @var string
     */
    protected string $columnPrefix;

    /**
     * @param string $tablePrefix
     * @param string $columnPrefix
     */
    public function __construct(string $tablePrefix, string $columnPrefix)
    {
        $this->strategy = null;
        $this->tablePrefix = $tablePrefix;
        $this->columnPrefix = $columnPrefix;
    }

    /**
     * @param string $strategyType
     * @param array $arguments
     * @return void
     */
    public function setStrategy(string $strategyType, array $arguments = []): void
    {
        $strategyClass = $this->container->getParameter($strategyType.'.class');
        $this->strategy = new $strategyClass(...$arguments);
    }

    /**
     * @param string $className
     * @return string
     */
    public function classToTableName($className): string
    {
        return $this->tablePrefix . $this->strategy->classToTableName($className);
    }

    /**
     * @param string $propertyName
     * @param string|null $className
     * @return string
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $this->columnPrefix . $this->strategy->propertyToColumnName($propertyName, $className);
    }

    /**
     * @param string $propertyName
     * @param string $embeddedColumnName
     * @param string|null $className
     * @param string|null $embeddedClassName
     * @return string
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        return $this->columnPrefix . $this->strategy->embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className, $embeddedClassName);
    }

    /**
     * @return string
     */
    public function referenceColumnName(): string
    {
        return $this->columnPrefix . $this->strategy->referenceColumnName();
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public function joinColumnName($propertyName/*, $className = null*/): string
    {
        return $this->columnPrefix . $this->strategy->joinColumnName($propertyName);
    }

    /**
     * @param string $sourceEntity
     * @param string $targetEntity
     * @param string|null $propertyName
     * @return string
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return $this->tablePrefix . $this->strategy->joinTableName($sourceEntity, $targetEntity, $propertyName);
    }

    /**
     * @param string $entityName
     * @param string|null $referencedColumnName
     * @return string
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return $this->columnPrefix . $this->strategy->joinKeyColumnName($entityName, $referencedColumnName);
    }
}
