<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

/**
 * Defines the metadata and structure for an entity in Twenty CRM.
 *
 * This class holds all the information needed to work with a specific
 * entity type (person, company, campaign, etc.) including its fields,
 * API endpoint, and relations.
 */
class EntityDefinition
{
    /**
     * Maps entity field names to API field names.
     * For RELATION fields: 'company' => 'companyId'
     *
     * @var array<string, string>
     */
    private array $fieldToApiMap = [];

    /**
     * Maps API field names to entity field names.
     * For RELATION fields: 'companyId' => 'company'
     *
     * @var array<string, string>
     */
    private array $apiToFieldMap = [];

    /**
     * @param string $objectName The singular object name (e.g., 'person', 'company', 'campaign')
     * @param string $objectNamePlural The plural object name (e.g., 'people', 'companies', 'campaigns')
     * @param string $apiEndpoint The REST API endpoint (e.g., '/people', '/companies')
     * @param array<string, FieldMetadata> $fields Associative array of field name => FieldMetadata
     * @param string[] $standardFields List of standard (non-custom) field names
     * @param array<string, mixed> $nestedObjectMap Map of field names to their nested object handlers
     * @param array<string, RelationMetadata> $relations Associative array of relation name => RelationMetadata
     */
    public function __construct(
        public readonly string $objectName,
        public readonly string $objectNamePlural,
        public readonly string $apiEndpoint,
        public readonly array $fields,
        public readonly array $standardFields,
        public readonly array $nestedObjectMap = [],
        public readonly array $relations = [],
    ) {
        $this->buildFieldMaps();
    }

    /**
     * Build the bidirectional field name mapping tables.
     *
     * For RELATION fields, the entity uses 'company' but API uses 'companyId'.
     *
     * @return void
     */
    private function buildFieldMaps(): void
    {
        foreach ($this->fields as $fieldName => $field) {
            if ($field->type->isRelation()) {
                // Entity field 'company' maps to API field 'companyId'
                $apiFieldName = $fieldName . 'Id';
                $this->fieldToApiMap[$fieldName] = $apiFieldName;
                $this->apiToFieldMap[$apiFieldName] = $fieldName;
            }
        }
    }

    /**
     * Get a field by name.
     *
     * @param string $name The field name
     * @return FieldMetadata|null The field metadata or null if not found
     */
    public function getField(string $name): ?FieldMetadata
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Check if a field exists.
     *
     * @param string $name The field name
     * @return bool True if the field exists
     */
    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * Get all field names.
     *
     * @return string[] Array of field names
     */
    public function getFieldNames(): array
    {
        return array_keys($this->fields);
    }

    /**
     * Get required (non-nullable) fields.
     *
     * @return FieldMetadata[] Array of required field metadata
     */
    public function getRequiredFields(): array
    {
        return array_filter(
            $this->fields,
            fn (FieldMetadata $field) => $field->isRequired()
        );
    }

    /**
     * Get custom fields (non-standard fields).
     *
     * @return FieldMetadata[] Array of custom field metadata
     */
    public function getCustomFields(): array
    {
        return array_filter(
            $this->fields,
            fn (FieldMetadata $field, string $name) => !in_array($name, $this->standardFields, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Check if a field is a standard field.
     *
     * @param string $name The field name
     * @return bool True if the field is standard (not custom)
     */
    public function isStandardField(string $name): bool
    {
        return in_array($name, $this->standardFields, true);
    }

    /**
     * Check if a field is a custom field.
     *
     * @param string $name The field name
     * @return bool True if the field is custom (not standard)
     */
    public function isCustomField(string $name): bool
    {
        return $this->hasField($name) && !$this->isStandardField($name);
    }

    /**
     * Get a relation by name.
     *
     * @param string $name The relation name
     * @return RelationMetadata|null The relation metadata or null if not found
     */
    public function getRelation(string $name): ?RelationMetadata
    {
        return $this->relations[$name] ?? null;
    }

    /**
     * Check if a relation exists.
     *
     * @param string $name The relation name
     * @return bool True if the relation exists
     */
    public function hasRelation(string $name): bool
    {
        return isset($this->relations[$name]);
    }

    /**
     * Get all relations.
     *
     * @return array<string, RelationMetadata> Associative array of relation name => RelationMetadata
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Get relation names.
     *
     * @return string[] Array of relation names
     */
    public function getRelationNames(): array
    {
        return array_keys($this->relations);
    }

    /**
     * Map an entity field name to its API field name.
     *
     * For most fields, this returns the same name.
     * For RELATION fields, converts 'company' => 'companyId'.
     *
     * @param string $fieldName Entity field name
     * @return string API field name
     */
    public function mapFieldToApi(string $fieldName): string
    {
        return $this->fieldToApiMap[$fieldName] ?? $fieldName;
    }

    /**
     * Map an API field name to its entity field name.
     *
     * For most fields, this returns the same name.
     * For RELATION fields, converts 'companyId' => 'company'.
     *
     * @param string $apiFieldName API field name
     * @return string Entity field name
     */
    public function mapApiToField(string $apiFieldName): string
    {
        return $this->apiToFieldMap[$apiFieldName] ?? $apiFieldName;
    }
}
