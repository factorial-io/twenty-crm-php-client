<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * Generic entity service for CRUD operations on any Twenty CRM entity.
 *
 * This service works with DynamicEntity and EntityDefinition to provide
 * flexible entity operations without hardcoded DTOs.
 */
class GenericEntityService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityDefinition $definition,
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
     * @return DynamicEntity[] Array of entities
     */
    public function find(FilterInterface $filter, SearchOptions $options): array
    {
        $queryParams = $options->toQueryParams();

        // Add filter if any filters are set
        if ($filter->hasFilters()) {
            $queryParams['filter'] = $filter->buildFilterString();
        }

        $requestOptions = ['query' => $queryParams];

        $response = $this->httpClient->request('GET', $this->definition->apiEndpoint, $requestOptions);

        return $this->parseCollectionResponse($response);
    }

    /**
     * Get an entity by ID.
     *
     * @param string $id The entity ID
     * @return DynamicEntity|null The entity or null if not found
     */
    public function getById(string $id): ?DynamicEntity
    {
        try {
            $response = $this->httpClient->request('GET', $this->definition->apiEndpoint . '/' . $id);

            return $this->parseEntityResponse($response);
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Create a new entity.
     *
     * @param DynamicEntity $entity The entity to create
     * @return DynamicEntity The created entity with ID
     */
    public function create(DynamicEntity $entity): DynamicEntity
    {
        $data = $entity->toArray();

        $response = $this->httpClient->request('POST', $this->definition->apiEndpoint, [
            'json' => $data,
        ]);

        return $this->parseEntityResponse($response);
    }

    /**
     * Update an existing entity.
     *
     * @param DynamicEntity $entity The entity to update (must have ID)
     * @return DynamicEntity The updated entity
     * @throws \InvalidArgumentException If entity has no ID
     */
    public function update(DynamicEntity $entity): DynamicEntity
    {
        $id = $entity->getId();
        if (!$id) {
            throw new \InvalidArgumentException('Entity must have an ID to be updated');
        }

        $data = $entity->toArray();
        unset($data['id']); // Remove ID from body as it's in the URL

        $response = $this->httpClient->request('PATCH', $this->definition->apiEndpoint . '/' . $id, [
            'json' => $data,
        ]);

        return $this->parseEntityResponse($response);
    }

    /**
     * Delete an entity by ID.
     *
     * @param string $id The entity ID
     * @return bool True if deleted, false if not found
     */
    public function delete(string $id): bool
    {
        try {
            $this->httpClient->request('DELETE', $this->definition->apiEndpoint . '/' . $id);

            return true;
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Batch upsert entities.
     *
     * @param DynamicEntity[] $entities The entities to upsert
     * @return DynamicEntity[] The upserted entities
     */
    public function batchUpsert(array $entities): array
    {
        $data = array_map(fn (DynamicEntity $entity) => $entity->toArray(), $entities);

        $response = $this->httpClient->request('POST', '/batch' . $this->definition->apiEndpoint, [
            'json' => ['data' => $data],
        ]);

        return $this->parseCollectionResponse($response);
    }

    /**
     * Parse a collection response from the API.
     *
     * @param array<string, mixed> $response The API response
     * @return DynamicEntity[] Array of entities
     */
    private function parseCollectionResponse(array $response): array
    {
        $data = $response['data'][$this->definition->objectNamePlural] ?? [];

        if (!is_array($data)) {
            return [];
        }

        $entities = [];
        foreach ($data as $itemData) {
            $entities[] = DynamicEntity::fromArray($itemData, $this->definition);
        }

        return $entities;
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
