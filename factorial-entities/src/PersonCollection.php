<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entities;

use Factorial\TwentyCrm\DTO\DynamicEntityCollection;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * PersonCollection (auto-generated).
 *
 * Typed collection of Person entities.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
final class PersonCollection
{
    /** @var Person[] */
    private array $entities;

    public function __construct(
        /** @param Person[] $entities */
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
            $entities[] = new Person($definition, $dynamicEntity->toArray());
        }

        return new self($entities);
    }

    /**
     * Get all entities in the collection.
     *
     * @return Person[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Get all Person entities.
     *
     * @return Person[]
     */
    public function getPersons(): array
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
     * @return Person|null
     */
    public function first(): ?Person
    {
        return $this->entities[0] ?? null;
    }
}
