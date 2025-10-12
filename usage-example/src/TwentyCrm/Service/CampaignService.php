<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Service;

use Factorial\TwentyCrm\Collection\CampaignCollection;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Campaign;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Services\GenericEntityService;

/**
 * CampaignService (auto-generated).
 *
 * Provides typed access to Campaign CRUD operations.
 * Wraps GenericEntityService with entity-specific types.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
final class CampaignService
{
    private readonly GenericEntityService $genericService;
    private readonly EntityDefinition $definition;

    public function __construct(HttpClientInterface $httpClient, EntityDefinition $definition)
    {
        $this->definition = $definition;
        $this->genericService = new GenericEntityService($httpClient, $definition);
    }

    /**
     * Create a new empty Campaign instance.
     *
     * @return Campaign
     */
    public function createInstance(): \Campaign
    {
        return new Campaign($this->definition);
    }

    /**
     * Find Campaign entities matching filter.
     *
     * @param FilterInterface $filter Search filter
     * @param SearchOptions $options Search options
     * @return CampaignCollection
     */
    public function find(FilterInterface $filter, SearchOptions $options): \CampaignCollection
    {
        $dynamicEntities = $this->genericService->find($filter, $options);
        $entities = [];

        foreach ($dynamicEntities as $dynamicEntity) {
            $entities[] = new Campaign($this->definition, $dynamicEntity->toArray());
        }

        return new CampaignCollection($entities);
    }

    /**
     * Get Campaign by ID.
     *
     * @param string $id Entity ID
     * @return Campaign|null
     */
    public function getById(string $id): ?\Campaign
    {
        $entity = $this->genericService->getById($id);

        if ($entity === null) {
            return null;
        }

        return new Campaign($this->definition, $entity->toArray());
    }

    /**
     * Create a new Campaign.
     *
     * @param Campaign $entity Entity to create
     * @return Campaign
     */
    public function create(\Campaign $entity): \Campaign
    {
        $created = $this->genericService->create($entity);
        return new Campaign($this->definition, $created->toArray());
    }

    /**
     * Update an existing Campaign.
     *
     * @param Campaign $entity Entity to update
     * @return Campaign
     */
    public function update(\Campaign $entity): \Campaign
    {
        $updated = $this->genericService->update($entity);
        return new Campaign($this->definition, $updated->toArray());
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
     * Batch upsert multiple Campaign entities.
     *
     * @param Campaign[] $entities Entities to upsert
     * @return CampaignCollection
     */
    public function batchUpsert(array $entities): \CampaignCollection
    {
        $dynamicEntities = $this->genericService->batchUpsert($entities);
        $typedEntities = [];

        foreach ($dynamicEntities as $dynamicEntity) {
            $typedEntities[] = new Campaign($this->definition, $dynamicEntity->toArray());
        }

        return new CampaignCollection($typedEntities);
    }
}
