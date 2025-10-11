<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entities;

use Factorial\TwentyCrm\DTO\DynamicEntityCollection;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * CompanyCollection (auto-generated).
 *
 * Typed collection of Company entities.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
final class CompanyCollection
{
    /** @var Company[] */
    private array $entities;

    public function __construct(
        /** @param Company[] $entities */
        array $entities,
    ) {
        $this->entities = $entities;
    }

    /**
     * Create typed collection from DynamicEntityCollection.
     *
     * @param DynamicEntityCollection $collection
     * @return self
     */
    public static function fromDynamicCollection(DynamicEntityCollection $collection): self
    {
        $definition = $collection->getDefinition();
        $entities = [];

        foreach ($collection->getEntities() as $dynamicEntity) {
            $entities[] = new Company($definition, $dynamicEntity->toArray());
        }

        return new self($entities);
    }

    /**
     * Get all entities in the collection.
     *
     * @return Company[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Get all Company entities.
     *
     * @return Company[]
     */
    public function getCompanies(): array
    {
        return $this->entities;
    }

    /**
     * Get the number of entities in the collection.
     */
    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->entities);
    }

    /**
     * Get the first entity in the collection.
     *
     * @return Company|null
     */
    public function first(): ?Company
    {
        return $this->entities[0] ?? null;
    }
}
