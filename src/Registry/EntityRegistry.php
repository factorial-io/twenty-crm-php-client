<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Registry;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\FieldMetadata;
use Factorial\TwentyCrm\Metadata\FieldMetadataFactory;
use Factorial\TwentyCrm\Metadata\RelationMetadata;
use Factorial\TwentyCrm\Services\MetadataService;

/**
 * Registry for discovering and managing entity definitions from Twenty CRM.
 *
 * This class fetches metadata from the Twenty API and builds EntityDefinition
 * objects for each entity type (person, company, campaign, etc.).
 */
class EntityRegistry
{
    /**
     * Cache of entity definitions by object name.
     *
     * @var array<string, EntityDefinition>
     */
    private array $definitions = [];

    /**
     * Whether entities have been discovered.
     */
    private bool $discovered = false;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MetadataService $metadata,
    ) {
    }

    /**
     * Get an entity definition by object name.
     *
     * @param string $objectName The singular object name (e.g., 'person', 'company')
     * @return EntityDefinition|null The entity definition or null if not found
     */
    public function getDefinition(string $objectName): ?EntityDefinition
    {
        $this->ensureDiscovered();

        return $this->definitions[$objectName] ?? null;
    }

    /**
     * Check if an entity exists.
     *
     * @param string $objectName The singular object name
     * @return bool True if the entity exists
     */
    public function hasEntity(string $objectName): bool
    {
        $this->ensureDiscovered();

        return isset($this->definitions[$objectName]);
    }

    /**
     * Get all entity names.
     *
     * @return string[] Array of entity names
     */
    public function getAllEntityNames(): array
    {
        $this->ensureDiscovered();

        return array_keys($this->definitions);
    }

    /**
     * Get all entity definitions.
     *
     * @return array<string, EntityDefinition> Associative array of entity name => definition
     */
    public function getAllDefinitions(): array
    {
        $this->ensureDiscovered();

        return $this->definitions;
    }

    /**
     * Clear the registry cache and force re-discovery.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->definitions = [];
        $this->discovered = false;
        $this->metadata->clearCache();
    }

    /**
     * Ensure entities have been discovered.
     *
     * @return void
     */
    private function ensureDiscovered(): void
    {
        if (!$this->discovered) {
            $this->discoverEntities();
            $this->discovered = true;
        }
    }

    /**
     * Discover all entities from the Twenty CRM metadata API.
     *
     * @return void
     */
    private function discoverEntities(): void
    {
        try {
            $response = $this->httpClient->request('GET', 'metadata/objects');

            if (!isset($response['data']['objects']) || !is_array($response['data']['objects'])) {
                return;
            }

            foreach ($response['data']['objects'] as $objectData) {
                $definition = $this->buildEntityDefinition($objectData);
                if ($definition) {
                    $this->definitions[$definition->objectName] = $definition;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't throw - allow graceful degradation
            // In production, you might want to log this
        }
    }

    /**
     * Build an EntityDefinition from object metadata.
     *
     * @param array<string, mixed> $objectData The object metadata from the API
     * @return EntityDefinition|null The entity definition or null if invalid
     */
    private function buildEntityDefinition(array $objectData): ?EntityDefinition
    {
        $objectName = $objectData['nameSingular'] ?? null;
        $objectNamePlural = $objectData['namePlural'] ?? null;

        if (!$objectName || !$objectNamePlural) {
            return null;
        }

        // Build API endpoint from plural name
        $apiEndpoint = '/' . $objectNamePlural;

        // Extract fields
        $fields = $this->extractFields($objectData);

        // Extract standard fields (non-custom fields)
        $standardFields = $this->extractStandardFieldNames($fields);

        // Extract relations (will be implemented in Phase 6)
        $relations = $this->extractRelations($objectData);

        return new EntityDefinition(
            objectName: $objectName,
            objectNamePlural: $objectNamePlural,
            apiEndpoint: $apiEndpoint,
            fields: $fields,
            standardFields: $standardFields,
            nestedObjectMap: [], // Will be populated with field handlers in Phase 4
            relations: $relations,
        );
    }

    /**
     * Extract field metadata from object data.
     *
     * @param array<string, mixed> $objectData The object metadata
     * @return array<string, FieldMetadata> Associative array of field name => FieldMetadata
     */
    private function extractFields(array $objectData): array
    {
        $fields = [];

        if (!isset($objectData['fields']) || !is_array($objectData['fields'])) {
            return $fields;
        }

        foreach ($objectData['fields'] as $fieldData) {
            $fieldName = $fieldData['name'] ?? null;
            if (!$fieldName) {
                continue;
            }

            // Add objectMetadataId to field data
            $fieldData['objectMetadataId'] = $objectData['id'] ?? '';

            try {
                $field = FieldMetadataFactory::fromArray($fieldData);
                $fields[$fieldName] = $field;
            } catch (\Exception $e) {
                // Skip invalid fields
                continue;
            }
        }

        return $fields;
    }

    /**
     * Extract standard (non-custom) field names.
     *
     * @param array<string, FieldMetadata> $fields The field metadata
     * @return string[] Array of standard field names
     */
    private function extractStandardFieldNames(array $fields): array
    {
        $standardFields = [];

        foreach ($fields as $fieldName => $field) {
            if (!$field->isCustom) {
                $standardFields[] = $fieldName;
            }
        }

        return $standardFields;
    }

    /**
     * Extract relation metadata from object data.
     *
     * @param array<string, mixed> $objectData The object metadata
     * @return array<string, RelationMetadata> Associative array of relation name => RelationMetadata
     */
    private function extractRelations(array $objectData): array
    {
        $relations = [];

        if (!isset($objectData['fields']) || !is_array($objectData['fields'])) {
            return $relations;
        }

        foreach ($objectData['fields'] as $fieldData) {
            $fieldName = $fieldData['name'] ?? null;
            $fieldType = $fieldData['type'] ?? null;

            if (!$fieldName || !$fieldType) {
                continue;
            }

            // Check if this is a relation field
            if (!$this->isRelationField($fieldType)) {
                continue;
            }

            try {
                $relation = $this->buildRelationMetadata($fieldName, $fieldData);
                if ($relation) {
                    $relations[$fieldName] = $relation;
                }
            } catch (\Exception $e) {
                // Skip invalid relations
                continue;
            }
        }

        return $relations;
    }

    /**
     * Check if a field type represents a relation.
     *
     * @param string $fieldType The field type
     * @return bool True if this is a relation field
     */
    private function isRelationField(string $fieldType): bool
    {
        // In Twenty CRM API, relation fields have type 'RELATION'
        return $fieldType === 'RELATION';
    }

    /**
     * Build RelationMetadata from field data.
     *
     * @param string $fieldName The field name
     * @param array<string, mixed> $fieldData The field metadata
     * @return RelationMetadata|null The relation metadata or null if invalid
     */
    private function buildRelationMetadata(string $fieldName, array $fieldData): ?RelationMetadata
    {
        // Use the factory method from RelationMetadata
        return RelationMetadata::fromApiMetadata($fieldData);
    }
}
