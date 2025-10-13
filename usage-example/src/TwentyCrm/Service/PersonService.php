<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Service;

use Factorial\TwentyCrm\Collection\PersonCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Person;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Query\FilterInterface;
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

    public function __construct(HttpClientInterface $httpClient)
    {
        // Create EntityDefinition from static entity metadata
        $definition = Person::createDefinition();
        $this->genericService = new GenericEntityService($httpClient, $definition);
    }

    /**
     * Create a new Person instance.
     *
     * @param array $data Optional initial data for the entity
     * @return Person
     */
    public function createInstance(array $data = []): Person
    {
        return new Person($data);
    }

    /**
     * Find Person entities matching filter.
     *
     * @param FilterInterface $filter Search filter
     * @param SearchOptions $options Search options
     * @return PersonCollection
     */
    public function find(FilterInterface $filter, SearchOptions $options): PersonCollection
    {
        $dynamicCollection = $this->genericService->find($filter, $options);
        return PersonCollection::fromDynamicCollection($dynamicCollection);
    }

    /**
     * Get Person by ID.
     *
     * @param string $id Entity ID
     * @return Person|null
     */
    public function getById(string $id): ?Person
    {
        $entity = $this->genericService->getById($id);

        if ($entity === null) {
            return null;
        }

        return new Person($entity->toArray());
    }

    /**
     * Create a new Person.
     *
     * @param Person $entity Entity to create
     * @return Person
     */
    public function create(Person $entity): Person
    {
        $created = $this->genericService->create($entity);
        return new Person($created->toArray());
    }

    /**
     * Update an existing Person.
     *
     * @param Person $entity Entity to update
     * @return Person
     */
    public function update(Person $entity): Person
    {
        $updated = $this->genericService->update($entity);
        return new Person($updated->toArray());
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
    public function batchUpsert(array $entities): PersonCollection
    {
        $dynamicCollection = $this->genericService->batchUpsert($entities);
        return PersonCollection::fromDynamicCollection($dynamicCollection);
    }
}
