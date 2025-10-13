<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\FieldMetadataFactory;

/**
 * Base class for statically-generated entities with baked-in metadata.
 *
 * Unlike DynamicEntity which requires EntityDefinition at runtime,
 * StaticEntity uses abstract static methods that generated entities
 * implement with their metadata. This removes the need for runtime
 * API calls to fetch metadata.
 *
 * Generated entity classes extend this and provide:
 * - Static metadata about fields (type, nullability, handlers)
 * - Field name mappings (entity field <-> API field)
 * - Entity name and API endpoint information
 *
 * This approach "bakes in" all metadata at code generation time,
 * making generated entities completely self-contained.
 */
abstract class StaticEntity extends AbstractEntity
{
    /**
     * @param array<string, mixed> $data The entity data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    // ====================================================================
    // Abstract static methods that generated entities must implement
    // ====================================================================

    /**
     * Get the entity name (singular).
     *
     * @return string Entity name (e.g., 'person', 'company')
     */
    abstract protected static function getEntityName(): string;

    /**
     * Get the entity name (plural).
     *
     * @return string Plural entity name (e.g., 'people', 'companies')
     */
    abstract protected static function getEntityNamePlural(): string;

    /**
     * Get the API endpoint for this entity.
     *
     * @return string API endpoint (e.g., '/people', '/companies')
     */
    abstract protected static function getApiEndpoint(): string;

    /**
     * Get metadata for a specific field.
     *
     * Returns an array with keys:
     * - type: FieldType enum value
     * - nullable: bool
     * - hasHandler: bool (true if field has a handler)
     * - isCustom: bool (true if custom field)
     * - isSystem: bool (true if system-managed field)
     * - label: string (field label)
     * - description: string|null (field description)
     * - defaultValue: mixed (default value)
     * - objectMetadataId: string (parent object ID)
     * - relationDefinition: array|null (relation metadata)
     * - options: array|null (field options)
     * - apiField: string|null (API field name if different)
     *
     * @param string $fieldName The field name
     * @return array<string, mixed>|null Field metadata or null if field doesn't exist
     */
    abstract protected static function getFieldMetadata(string $fieldName): ?array;

    /**
     * Get all field names.
     *
     * @return string[] Array of field names
     */
    abstract protected static function getAllFieldNames(): array;

    /**
     * Get field-to-API mapping.
     *
     * Returns array of entity field name => API field name for fields
     * where they differ (primarily RELATION fields).
     *
     * @return array<string, string> Map of field name => API field name
     */
    abstract protected static function getFieldToApiMap(): array;

    /**
     * Get API-to-field mapping.
     *
     * Returns array of API field name => entity field name for fields
     * where they differ (primarily RELATION fields).
     *
     * @return array<string, string> Map of API field name => field name
     */
    abstract protected static function getApiToFieldMap(): array;

    // ====================================================================
    // AbstractEntity implementation - metadata access via static methods
    // ====================================================================

    /**
     * Get field metadata from static methods.
     *
     * @param string $fieldName The field name
     * @return array<string, mixed>|null Field metadata or null if not found
     */
    protected function getFieldMetadataArray(string $fieldName): ?array
    {
        return static::getFieldMetadata($fieldName);
    }

    /**
     * Map entity field name to API field name using static map.
     *
     * @param string $fieldName Entity field name
     * @return string API field name
     */
    protected function mapFieldToApi(string $fieldName): string
    {
        $map = static::getFieldToApiMap();
        return $map[$fieldName] ?? $fieldName;
    }

    /**
     * Map API field name to entity field name using static map.
     *
     * @param string $apiFieldName API field name
     * @return string Entity field name
     */
    protected function mapApiToField(string $apiFieldName): string
    {
        $map = static::getApiToFieldMap();
        return $map[$apiFieldName] ?? $apiFieldName;
    }

    // ====================================================================
    // Compatibility with EntityDefinition-based code
    // ====================================================================

    /**
     * Create an EntityDefinition from the static metadata.
     *
     * This allows StaticEntity-based classes to work with code
     * that expects EntityDefinition (like GenericEntityService).
     *
     * @return EntityDefinition
     */
    public static function createDefinition(): EntityDefinition
    {
        $fields = [];
        $standardFields = [];

        foreach (static::getAllFieldNames() as $fieldName) {
            $metadata = static::getFieldMetadata($fieldName);
            if (!$metadata) {
                continue;
            }

            // Create FieldMetadata using factory from the static metadata
            $fieldData = [
                'id' => $metadata['id'] ?? '',
                'name' => $fieldName,
                'type' => $metadata['type']->value,
                'label' => $metadata['label'] ?? $fieldName,
                'objectMetadataId' => $metadata['objectMetadataId'] ?? '',
                'isNullable' => $metadata['nullable'],
                'description' => $metadata['description'] ?? null,
                'icon' => $metadata['icon'] ?? null,
                'defaultValue' => $metadata['defaultValue'] ?? null,
                'isCustom' => $metadata['isCustom'] ?? false,
                'isActive' => $metadata['isActive'] ?? true,
                'isSystem' => $metadata['isSystem'] ?? false,
                'relationDefinition' => $metadata['relationDefinition'] ?? null,
                'options' => $metadata['options'] ?? null,
            ];

            $fields[$fieldName] = FieldMetadataFactory::fromArray($fieldData);

            if (!($metadata['isCustom'] ?? false)) {
                $standardFields[] = $fieldName;
            }
        }

        return new EntityDefinition(
            objectName: static::getEntityName(),
            objectNamePlural: static::getEntityNamePlural(),
            apiEndpoint: static::getApiEndpoint(),
            fields: $fields,
            standardFields: $standardFields,
            nestedObjectMap: [],
            relations: [],
        );
    }
}
