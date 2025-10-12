<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Collection;

use Factorial\TwentyCrm\Entity\DynamicEntity;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * A collection of dynamic entities.
 *
 * This class provides a flexible way to work with entity collections
 * without requiring typed collection classes. It can be converted to
 * typed collections using the generated collection classes.
 *
 * Example:
 *   $dynamicCollection = new DynamicEntityCollection($definition, $entities);
 *   $typedCollection = PersonCollection::fromDynamicCollection($dynamicCollection);
 */
class DynamicEntityCollection implements \Countable, \IteratorAggregate
{
    /**
     * The entities in this collection.
     *
     * @var DynamicEntity[]
     */
    private array $entities;

    /**
     * @param EntityDefinition $definition The entity definition
     * @param DynamicEntity[] $entities The entities
     */
    public function __construct(
        private readonly EntityDefinition $definition,
        array $entities = [],
    ) {
        $this->entities = $entities;
    }

    /**
     * Get the entity definition.
     *
     * @return EntityDefinition
     */
    public function getDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    /**
     * Get all entities in the collection.
     *
     * @return DynamicEntity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Get the number of entities in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->entities);
    }

    /**
     * Get the first entity in the collection.
     *
     * @return DynamicEntity|null
     */
    public function first(): ?DynamicEntity
    {
        return $this->entities[0] ?? null;
    }

    /**
     * Add an entity to the collection.
     *
     * @param DynamicEntity $entity The entity to add
     * @return void
     */
    public function add(DynamicEntity $entity): void
    {
        $this->entities[] = $entity;
    }

    /**
     * Convert all entities to arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn (DynamicEntity $entity) => $entity->toArray(), $this->entities);
    }

    /**
     * Create a collection from an array of entity data.
     *
     * @param EntityDefinition $definition The entity definition
     * @param array<int, array<string, mixed>> $data Array of entity data
     * @return self
     */
    public static function fromArray(EntityDefinition $definition, array $data): self
    {
        $entities = array_map(
            fn (array $entityData) => new DynamicEntity($definition, $entityData),
            $data
        );

        return new self($definition, $entities);
    }

    /**
     * Get an iterator for the entities.
     *
     * @return \Traversable<int, DynamicEntity>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->entities);
    }
}
