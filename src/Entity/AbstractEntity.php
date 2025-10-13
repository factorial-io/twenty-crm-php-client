<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\FieldHandlers\FieldHandlerRegistry;

/**
 * Abstract base class for all entity types (dynamic and static).
 *
 * Contains all common entity functionality including:
 * - Data storage and access
 * - Field transformations (via FieldHandlerRegistry)
 * - Relation management
 * - Array/Iterator/JSON interfaces
 *
 * Subclasses must implement metadata access methods to provide
 * field information (either from EntityDefinition or static methods).
 */
abstract class AbstractEntity implements \ArrayAccess, \IteratorAggregate, \JsonSerializable
{
    /**
     * The entity data.
     *
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * Loaded relations.
     *
     * @var array<string, mixed>
     */
    protected array $loadedRelations = [];

    /**
     * Field handler registry for complex type transformations.
     *
     * @var FieldHandlerRegistry|null
     */
    private static ?FieldHandlerRegistry $handlerRegistry = null;

    // ====================================================================
    // Abstract methods for metadata access
    // ====================================================================

    /**
     * Get metadata for a specific field.
     *
     * Returns an array with keys:
     * - type: FieldType enum value
     * - nullable: bool
     * - handler: bool (true if this field has a handler)
     *
     * @param string $fieldName The field name
     * @return array<string, mixed>|null Field metadata or null if field doesn't exist
     */
    abstract protected function getFieldMetadataArray(string $fieldName): ?array;

    /**
     * Map an entity field name to its API field name.
     *
     * For most fields, this returns the same name.
     * For RELATION fields, converts 'company' => 'companyId'.
     *
     * @param string $fieldName Entity field name
     * @return string API field name
     */
    abstract protected function mapFieldToApi(string $fieldName): string;

    /**
     * Map an API field name to its entity field name.
     *
     * For most fields, this returns the same name.
     * For RELATION fields, converts 'companyId' => 'company'.
     *
     * @param string $apiFieldName API field name
     * @return string Entity field name
     */
    abstract protected function mapApiToField(string $apiFieldName): string;

    // ====================================================================
    // Data access methods
    // ====================================================================

    /**
     * Get a field value.
     *
     * Automatically transforms API arrays to PHP objects (PhoneCollection, etc.)
     * when field handlers are available.
     *
     * @param string $fieldName The field name
     * @return mixed The field value or null if not set
     */
    public function get(string $fieldName): mixed
    {
        // Try entity field name first, then API field name
        if (array_key_exists($fieldName, $this->data)) {
            $value = $this->data[$fieldName];
        } else {
            // Map to API field name (e.g., 'company' -> 'companyId')
            $apiFieldName = $this->mapFieldToApi($fieldName);
            $value = $this->data[$apiFieldName] ?? null;
        }

        if ($value === null) {
            return null;
        }

        // Get field metadata to determine type
        $metadata = $this->getFieldMetadataArray($fieldName);

        if (!$metadata) {
            // Unknown field, return as-is
            return $value;
        }

        $handlers = self::getHandlerRegistry();

        // If we have a handler and value is an array, transform to PHP object
        if (($metadata['hasHandler'] ?? false) && is_array($value)) {
            return $handlers->fromApi($metadata['type'], $value);
        }

        // Return as-is (already a PHP object or basic type)
        return $value;
    }

    /**
     * Set a field value.
     *
     * Uses field mapping for RELATION fields.
     *
     * @param string $fieldName The field name
     * @param mixed $value The field value
     * @return void
     */
    public function set(string $fieldName, mixed $value): void
    {
        // Remove the API field name if it exists (e.g., remove 'companyId' when setting 'company')
        $apiFieldName = $this->mapFieldToApi($fieldName);
        if ($apiFieldName !== $fieldName) {
            unset($this->data[$apiFieldName]);
        }

        $this->data[$fieldName] = $value;
    }

    /**
     * Check if a field is set.
     *
     * @param string $fieldName The field name
     * @return bool True if the field is set
     */
    public function has(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->data);
    }

    /**
     * Remove a field.
     *
     * @param string $fieldName The field name
     * @return void
     */
    public function unset(string $fieldName): void
    {
        unset($this->data[$fieldName]);
    }

    /**
     * Get all data as an array.
     *
     * Transforms complex PHP objects (PhoneCollection, LinkCollection, etc.)
     * back to API array format for sending to Twenty CRM.
     *
     * @return array<string, mixed> The entity data in API format
     */
    public function toArray(): array
    {
        $result = [];
        $handlers = self::getHandlerRegistry();

        foreach ($this->data as $fieldName => $value) {
            // Try to get field metadata using entity field name
            $entityFieldName = $this->mapApiToField($fieldName);
            $metadata = $this->getFieldMetadataArray($entityFieldName);

            if (!$metadata) {
                // Unknown field, pass through as-is
                $result[$fieldName] = $value;
                continue;
            }

            // Map to API field name
            $apiFieldName = $this->mapFieldToApi($entityFieldName);

            // Transform value if we have a handler
            if (($metadata['hasHandler'] ?? false) && $handlers->hasHandler($metadata['type'])) {
                $result[$apiFieldName] = $handlers->toApi($metadata['type'], $value);
            } else {
                $result[$apiFieldName] = $value;
            }
        }

        return $result;
    }

    /**
     * Get all field names.
     *
     * @return string[] Array of field names
     */
    public function getFieldNames(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get the entity ID (if present).
     *
     * @return string|null The entity ID or null if not set
     */
    public function getId(): ?string
    {
        return $this->get('id');
    }

    /**
     * Set the entity ID.
     *
     * @param string|null $id The entity ID
     * @return void
     */
    public function setId(?string $id): void
    {
        if ($id === null) {
            $this->unset('id');
        } else {
            $this->set('id', $id);
        }
    }

    // ====================================================================
    // Relation Management
    // ====================================================================

    /**
     * Get a loaded relation (doesn't trigger load).
     *
     * @param string $relationName The relation name
     * @return mixed The loaded relation or null if not loaded
     */
    public function getRelation(string $relationName): mixed
    {
        return $this->loadedRelations[$relationName] ?? null;
    }

    /**
     * Check if a relation is loaded.
     *
     * @param string $relationName The relation name
     * @return bool True if the relation is loaded
     */
    public function hasLoadedRelation(string $relationName): bool
    {
        return isset($this->loadedRelations[$relationName]);
    }

    /**
     * Set a relation (for eager loading).
     *
     * @param string $relationName The relation name
     * @param mixed $value The relation value
     * @return void
     */
    public function setRelation(string $relationName, mixed $value): void
    {
        $this->loadedRelations[$relationName] = $value;
    }

    /**
     * Get all loaded relations.
     *
     * @return array<string, mixed> Associative array of relation name => value
     */
    public function getLoadedRelations(): array
    {
        return $this->loadedRelations;
    }

    // ====================================================================
    // ArrayAccess Implementation
    // ====================================================================

    /**
     * Check if an offset exists.
     *
     * @param mixed $offset The offset to check
     * @return bool True if the offset exists
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    /**
     * Get the value at an offset.
     *
     * @param mixed $offset The offset
     * @return mixed The value
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    /**
     * Set the value at an offset.
     *
     * @param mixed $offset The offset
     * @param mixed $value The value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string) $offset, $value);
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset The offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unset((string) $offset);
    }

    // ====================================================================
    // IteratorAggregate Implementation
    // ====================================================================

    /**
     * Get an iterator for the data.
     *
     * @return \Traversable<string, mixed>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    // ====================================================================
    // JsonSerializable Implementation
    // ====================================================================

    /**
     * Serialize the entity to JSON.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    // ====================================================================
    // Field Handler Support
    // ====================================================================

    /**
     * Get the shared field handler registry.
     *
     * Uses lazy initialization to create registry on first use.
     *
     * @return FieldHandlerRegistry
     */
    protected static function getHandlerRegistry(): FieldHandlerRegistry
    {
        if (self::$handlerRegistry === null) {
            self::$handlerRegistry = new FieldHandlerRegistry();
        }

        return self::$handlerRegistry;
    }
}
