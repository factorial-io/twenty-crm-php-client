<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\RelationMetadata;
use Factorial\TwentyCrm\Registry\EntityRegistry;

/**
 * RelationLoader handles loading related entities.
 *
 * Supports:
 * - Lazy loading: Load relations on-demand via $entity->loadRelation('company')
 * - Eager loading: Pre-load relations with SearchOptions(with: ['company'])
 * - Bidirectional relations: Person � Company and Company � People
 */
final class RelationLoader
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityRegistry $registry,
    ) {
    }

    /**
     * Load a relation for a single entity (lazy loading).
     *
     * @param DynamicEntity $entity Source entity
     * @param RelationMetadata $relation Relation to load
     * @return DynamicEntity|DynamicEntity[]|null Loaded entity, array of entities, or null
     * @throws ApiException
     */
    public function loadRelation(DynamicEntity $entity, RelationMetadata $relation): DynamicEntity|array|null
    {
        // Get the target entity definition
        $targetDefinition = $this->registry->getDefinition($relation->targetObjectName);
        if (!$targetDefinition) {
            throw new ApiException("Target entity '{$relation->targetObjectName}' not found in registry");
        }

        // Create service for target entity
        $targetService = new GenericEntityService($this->httpClient, $targetDefinition);

        // Load based on relation type
        if ($relation->isManyToOne()) {
            return $this->loadManyToOne($entity, $relation, $targetService);
        }

        if ($relation->isOneToMany()) {
            return $this->loadOneToMany($entity, $relation, $targetService);
        }

        if ($relation->isManyToMany()) {
            return $this->loadManyToMany($entity, $relation, $targetService);
        }

        return null;
    }

    /**
     * Load a MANY_TO_ONE relation (e.g., Person � Company).
     *
     * Returns a single entity or null.
     *
     * @param DynamicEntity $entity Source entity
     * @param RelationMetadata $relation Relation metadata
     * @param GenericEntityService $targetService Service for target entity
     * @return DynamicEntity|null
     * @throws ApiException
     */
    private function loadManyToOne(
        DynamicEntity $entity,
        RelationMetadata $relation,
        GenericEntityService $targetService
    ): ?DynamicEntity {
        // For MANY_TO_ONE, the source entity stores the target ID
        // e.g., person.company stores the company ID
        $relationData = $entity->get($relation->name);

        if (!$relationData) {
            return null;
        }

        // Extract ID from relation data
        // In Twenty CRM, MANY_TO_ONE fields return the related entity data directly
        $targetId = is_array($relationData) ? ($relationData['id'] ?? null) : $relationData;

        if (!$targetId) {
            return null;
        }

        return $targetService->getById($targetId);
    }

    /**
     * Load a ONE_TO_MANY relation (e.g., Company � People).
     *
     * Returns an array of entities.
     *
     * @param DynamicEntity $entity Source entity
     * @param RelationMetadata $relation Relation metadata
     * @param GenericEntityService $targetService Service for target entity
     * @return DynamicEntity[]
     * @throws ApiException
     */
    private function loadOneToMany(
        DynamicEntity $entity,
        RelationMetadata $relation,
        GenericEntityService $targetService
    ): array {
        // For ONE_TO_MANY, we query the target entity where the foreign key matches source ID
        // e.g., find all people where person.company.id = $company->getId()

        // Build filter: targetFieldName.id equals source entity ID
        // Format: "company.id eq 'uuid'"
        $filterString = "{$relation->targetFieldName}.id eq '{$entity->getId()}'";
        $filter = new CustomFilter($filterString);

        $options = new SearchOptions(limit: 100); // TODO: Make configurable
        $results = $targetService->find($filter, $options);

        return $results;
    }

    /**
     * Load a MANY_TO_MANY relation (e.g., Campaign � People).
     *
     * Returns an array of entities.
     *
     * @param DynamicEntity $entity Source entity
     * @param RelationMetadata $relation Relation metadata
     * @param GenericEntityService $targetService Service for target entity
     * @return DynamicEntity[]
     * @throws ApiException
     */
    private function loadManyToMany(
        DynamicEntity $entity,
        RelationMetadata $relation,
        GenericEntityService $targetService
    ): array {
        // MANY_TO_MANY typically uses a junction table
        // Implementation depends on Twenty CRM's specific approach
        // For now, treat similar to ONE_TO_MANY
        return $this->loadOneToMany($entity, $relation, $targetService);
    }

    /**
     * Eager load relations for multiple entities.
     *
     * Given a collection of entities and a list of relation names,
     * load all related entities and attach them.
     *
     * @param DynamicEntity[] $entities Entities to load relations for
     * @param string[] $relationNames Names of relations to load
     * @param EntityDefinition $sourceDefinition Source entity definition
     * @return void
     * @throws ApiException
     */
    public function eagerLoadRelations(
        array $entities,
        array $relationNames,
        EntityDefinition $sourceDefinition
    ): void {
        foreach ($relationNames as $relationName) {
            $relation = $sourceDefinition->getRelation($relationName);
            if (!$relation) {
                continue; // Skip unknown relations
            }

            // Load relation for each entity
            foreach ($entities as $entity) {
                $loaded = $this->loadRelation($entity, $relation);
                // Store loaded relation on entity
                $entity->setRelation($relationName, $loaded);
            }
        }
    }
}
