<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Service;

use Factorial\TwentyCrm\Collection\CompanyCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Company;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Query\FilterInterface;
use Factorial\TwentyCrm\Services\GenericEntityService;

/**
 * CompanyService (auto-generated).
 *
 * Provides typed access to Company CRUD operations.
 * Wraps GenericEntityService with entity-specific types.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
final class CompanyService
{
    private readonly GenericEntityService $genericService;

    public function __construct(HttpClientInterface $httpClient)
    {
        // Create EntityDefinition from static entity metadata
        $definition = Company::createDefinition();
        $this->genericService = new GenericEntityService($httpClient, $definition);
    }

    /**
     * Create a new Company instance.
     *
     * @param array $data Optional initial data for the entity
     * @return Company
     */
    public function createInstance(array $data = []): Company
    {
        return new Company($data);
    }

    /**
     * Find Company entities matching filter.
     *
     * @param FilterInterface $filter Search filter
     * @param SearchOptions $options Search options
     * @return CompanyCollection
     */
    public function find(FilterInterface $filter, SearchOptions $options): CompanyCollection
    {
        $dynamicCollection = $this->genericService->find($filter, $options);
        return CompanyCollection::fromDynamicCollection($dynamicCollection);
    }

    /**
     * Get Company by ID.
     *
     * @param string $id Entity ID
     * @return Company|null
     */
    public function getById(string $id): ?Company
    {
        $entity = $this->genericService->getById($id);

        if ($entity === null) {
            return null;
        }

        return new Company($entity->toArray());
    }

    /**
     * Create a new Company.
     *
     * @param Company $entity Entity to create
     * @return Company
     */
    public function create(Company $entity): Company
    {
        $created = $this->genericService->create($entity);
        return new Company($created->toArray());
    }

    /**
     * Update an existing Company.
     *
     * @param Company $entity Entity to update
     * @return Company
     */
    public function update(Company $entity): Company
    {
        $updated = $this->genericService->update($entity);
        return new Company($updated->toArray());
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
     * Batch upsert multiple Company entities.
     *
     * @param Company[] $entities Entities to upsert
     * @return CompanyCollection
     */
    public function batchUpsert(array $entities): CompanyCollection
    {
        $dynamicCollection = $this->genericService->batchUpsert($entities);
        return CompanyCollection::fromDynamicCollection($dynamicCollection);
    }
}
