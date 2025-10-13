<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\Collection\DynamicEntityCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\AbstractEntity;
use Factorial\TwentyCrm\Entity\DynamicEntity;
use Factorial\TwentyCrm\Query\FilterInterface;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\FieldConstants;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Generic entity service for CRUD operations on any Twenty CRM entity.
 *
 * This service works with AbstractEntity and EntityDefinition to provide
 * flexible entity operations. Accepts both DynamicEntity (runtime) and
 * StaticEntity (code-generated) implementations.
 */
class GenericEntityService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityDefinition $definition,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
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
     * Find entities matching the filter and options.
     *
     * @param FilterInterface $filter The search filter
     * @param SearchOptions $options The search options
     * @return DynamicEntityCollection Collection of entities
     */
    public function find(FilterInterface $filter, SearchOptions $options): DynamicEntityCollection
    {
        $queryParams = $options->toQueryParams();

        // Add filter if any filters are set
        if ($filter->hasFilters()) {
            $queryParams['filter'] = $filter->buildFilterString();
        }

        $this->logger->debug('Finding entities', [
            'entity' => $this->definition->objectNamePlural,
            'filter' => $filter->buildFilterString(),
            'options' => $queryParams,
        ]);

        $requestOptions = ['query' => $queryParams];

        $response = $this->httpClient->request('GET', $this->definition->apiEndpoint, $requestOptions);

        $collection = $this->parseCollectionResponse($response);

        $this->logger->debug('Found entities', [
            'entity' => $this->definition->objectNamePlural,
            'count' => $collection->count(),
        ]);

        return $collection;
    }

    /**
     * Get an entity by ID.
     *
     * @param string $id The entity ID
     * @return DynamicEntity|null The entity or null if not found
     */
    public function getById(string $id): ?DynamicEntity
    {
        $this->logger->debug('Getting entity by ID', [
            'entity' => $this->definition->objectName,
            'id' => $id,
        ]);

        try {
            $response = $this->httpClient->request('GET', $this->definition->apiEndpoint . '/' . $id);

            $entity = $this->parseEntityResponse($response);

            $this->logger->debug('Entity found', [
                'entity' => $this->definition->objectName,
                'id' => $id,
            ]);

            return $entity;
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                $this->logger->debug('Entity not found', [
                    'entity' => $this->definition->objectName,
                    'id' => $id,
                ]);
                return null;
            }
            throw $e;
        }
    }

    /**
     * Create a new entity.
     *
     * @param AbstractEntity $entity The entity to create
     * @return DynamicEntity The created entity with ID
     */
    public function create(AbstractEntity $entity): DynamicEntity
    {
        $data = $entity->toArray();

        $this->logger->debug('Creating entity', [
            'entity' => $this->definition->objectName,
            'data' => $data,
        ]);

        $response = $this->httpClient->request('POST', $this->definition->apiEndpoint, [
            'json' => $data,
        ]);

        $created = $this->parseEntityResponse($response);

        $this->logger->debug('Entity created', [
            'entity' => $this->definition->objectName,
            'id' => $created->getId(),
        ]);

        return $created;
    }

    /**
     * Update an existing entity.
     *
     * @param AbstractEntity $entity The entity to update (must have ID)
     * @return DynamicEntity The updated entity
     * @throws \InvalidArgumentException If entity has no ID
     */
    public function update(AbstractEntity $entity): DynamicEntity
    {
        $id = $entity->getId();
        if (!$id) {
            $this->logger->error('Cannot update entity without ID', [
                'entity' => $this->definition->objectName,
            ]);
            throw new \InvalidArgumentException('Entity must have an ID to be updated');
        }

        $data = $entity->toArray();

        // Filter out read-only/system fields that shouldn't be updated
        $data = $this->filterUpdatableFields($data);

        $this->logger->debug('Updating entity', [
            'entity' => $this->definition->objectName,
            'id' => $id,
            'data' => $data,
        ]);

        $response = $this->httpClient->request('PATCH', $this->definition->apiEndpoint . '/' . $id, [
            'json' => $data,
        ]);

        $updated = $this->parseEntityResponse($response);

        $this->logger->debug('Entity updated', [
            'entity' => $this->definition->objectName,
            'id' => $id,
        ]);

        return $updated;
    }

    /**
     * Delete an entity by ID.
     *
     * @param string $id The entity ID
     * @return bool True if deleted, false if not found
     */
    public function delete(string $id): bool
    {
        $this->logger->debug('Deleting entity', [
            'entity' => $this->definition->objectName,
            'id' => $id,
        ]);

        try {
            $this->httpClient->request('DELETE', $this->definition->apiEndpoint . '/' . $id);

            $this->logger->debug('Entity deleted', [
                'entity' => $this->definition->objectName,
                'id' => $id,
            ]);

            return true;
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                $this->logger->debug('Entity not found for deletion', [
                    'entity' => $this->definition->objectName,
                    'id' => $id,
                ]);
                return false;
            }
            throw $e;
        }
    }

    /**
     * Batch upsert entities.
     *
     * @param AbstractEntity[] $entities The entities to upsert
     * @return DynamicEntityCollection The upserted entities
     */
    public function batchUpsert(array $entities): DynamicEntityCollection
    {
        $data = array_map(fn (AbstractEntity $entity) => $entity->toArray(), $entities);

        $this->logger->debug('Batch upserting entities', [
            'entity' => $this->definition->objectNamePlural,
            'count' => count($entities),
        ]);

        $response = $this->httpClient->request('POST', '/batch' . $this->definition->apiEndpoint, [
            'json' => ['data' => $data],
        ]);

        $collection = $this->parseCollectionResponse($response);

        $this->logger->debug('Batch upsert completed', [
            'entity' => $this->definition->objectNamePlural,
            'count' => $collection->count(),
        ]);

        return $collection;
    }

    /**
     * Filter entity data to only include updatable fields.
     *
     * Removes system-managed fields that shouldn't be sent in updates.
     * Uses centralized FieldConstants for consistent filtering logic.
     *
     * @param array<string, mixed> $data The entity data
     * @return array<string, mixed> Filtered data with only updatable fields
     * @see FieldConstants::filterUpdatableFields()
     */
    private function filterUpdatableFields(array $data): array
    {
        return FieldConstants::filterUpdatableFields($data, $this->definition->fields);
    }

    /**
     * Parse a collection response from the API.
     *
     * @param array<string, mixed> $response The API response
     * @return DynamicEntityCollection Collection of entities
     */
    private function parseCollectionResponse(array $response): DynamicEntityCollection
    {
        $data = $response['data'][$this->definition->objectNamePlural] ?? [];

        if (!is_array($data)) {
            return new DynamicEntityCollection($this->definition, []);
        }

        $entities = [];
        foreach ($data as $itemData) {
            $entities[] = DynamicEntity::fromArray($itemData, $this->definition);
        }

        return new DynamicEntityCollection($this->definition, $entities);
    }

    /**
     * Parse a single entity response from the API.
     *
     * @param array<string, mixed> $response The API response
     * @return DynamicEntity The entity
     */
    private function parseEntityResponse(array $response): DynamicEntity
    {
        // For GET /{entity}/{id}
        if (isset($response['data'][$this->definition->objectName])) {
            $data = $response['data'][$this->definition->objectName];
            return DynamicEntity::fromArray($data, $this->definition);
        }

        // For POST /{entity} (create) - response key is like "createPerson"
        $createKey = 'create' . ucfirst($this->definition->objectName);
        if (isset($response['data'][$createKey])) {
            $data = $response['data'][$createKey];
            return DynamicEntity::fromArray($data, $this->definition);
        }

        // For PATCH /{entity}/{id} (update) - response key is like "updatePerson"
        $updateKey = 'update' . ucfirst($this->definition->objectName);
        if (isset($response['data'][$updateKey])) {
            $data = $response['data'][$updateKey];
            return DynamicEntity::fromArray($data, $this->definition);
        }

        // Fallback: assume data is at root of response['data']
        if (isset($response['data']) && is_array($response['data'])) {
            return DynamicEntity::fromArray($response['data'], $this->definition);
        }

        throw new \RuntimeException('Unable to parse entity from API response');
    }
}
