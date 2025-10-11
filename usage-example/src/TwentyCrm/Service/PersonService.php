<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Service;

use Factorial\TwentyCrm\Collection\PersonCollection;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Person;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Services\GenericEntityService;

/**
 * PersonService (auto-generated).
 *
 * Provides typed access to Person CRUD operations.
 * Wraps GenericEntityService with entity-specific types.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
final class PersonService
{
    private readonly GenericEntityService $genericService;
    private readonly EntityDefinition $definition;

    public function __construct(HttpClientInterface $httpClient, EntityDefinition $definition)
    {
        $this->definition = $definition;
        $this->genericService = new GenericEntityService($httpClient, $definition);
    }

    /**
     * Find Person entities matching filter.
     *
     * @param FilterInterface $filter Search filter
     * @param SearchOptions $options Search options
     * @return PersonCollection
     */
    public function find(FilterInterface $filter, SearchOptions $options): \PersonCollection
    {
        $dynamicEntities = $this->genericService->find($filter, $options);
        $entities = [];

        foreach ($dynamicEntities as $dynamicEntity) {
            $entities[] = new Person($this->definition, $dynamicEntity->toArray());
        }

        return new PersonCollection($entities);
    }

    /**
     * Get Person by ID.
     *
     * @param string $id Entity ID
     * @return Person|null
     */
    public function getById(string $id): ?\Person
    {
        $entity = $this->genericService->getById($id);

        if ($entity === null) {
            return null;
        }

        return new Person($this->definition, $entity->toArray());
    }

    /**
     * Create a new Person.
     *
     * @param Person $entity Entity to create
     * @return Person
     */
    public function create(\Person $entity): \Person
    {
        $created = $this->genericService->create($entity);
        return new Person($this->definition, $created->toArray());
    }

    /**
     * Update an existing Person.
     *
     * @param Person $entity Entity to update
     * @return Person
     */
    public function update(\Person $entity): \Person
    {
        $updated = $this->genericService->update($entity);
        return new Person($this->definition, $updated->toArray());
    }

    /**
     * Delete an entity by ID.
     *
     * @param string $id Entity ID
     * @return bool True if deleted, false if not found
     */
    public function delete(string $id): bool
    {
        return $this->genericService->delete($id);
    }

    /**
     * Batch upsert multiple Person entities.
     *
     * @param Person[] $entities Entities to upsert
     * @return PersonCollection
     */
    public function batchUpsert(array $entities): \PersonCollection
    {
        $dynamicEntities = $this->genericService->batchUpsert($entities);
        $typedEntities = [];

        foreach ($dynamicEntities as $dynamicEntity) {
            $typedEntities[] = new Person($this->definition, $dynamicEntity->toArray());
        }

        return new PersonCollection($typedEntities);
    }
}
