<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

use Factorial\TwentyCrm\FieldHandlers\FieldHandlerRegistry;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * A dynamic entity that can represent any Twenty CRM object.
 *
 * This class provides a flexible way to work with entities without
 * requiring hardcoded DTOs. It stores data as an associative array
 * and uses EntityDefinition for metadata-driven validation and behavior.
 *
 * Implements ArrayAccess for convenient field access:
 *   $entity['fieldName'] or $entity->get('fieldName')
 *
 * Implements IteratorAggregate for foreach support:
 *   foreach ($entity as $field => $value) { ... }
 */
class DynamicEntity implements \ArrayAccess, \IteratorAggregate, \JsonSerializable
{
    /**
     * The entity data.
     *
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * Loaded relations.
     *
     * @var array<string, mixed>
     */
    private array $loadedRelations = [];

    /**
     * Field handler registry for complex type transformations.
     *
     * @var FieldHandlerRegistry|null
     */
    private static ?FieldHandlerRegistry $handlerRegistry = null;

    /**
     * @param EntityDefinition $definition The entity definition
     * @param array<string, mixed> $data The entity data
     */
    public function __construct(
        private readonly EntityDefinition $definition,
        array $data = [],
    ) {
        $this->data = $data;
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
        $value = $this->data[$fieldName] ?? null;

        if ($value === null) {
            return null;
        }

        // Get field metadata to determine type
        $field = $this->definition->getField($fieldName);

        if (!$field) {
            // Unknown field, return as-is
            return $value;
        }

        $handlers = self::getHandlerRegistry();

        // If we have a handler and value is an array, transform to PHP object
        if ($handlers->hasHandler($field->type) && is_array($value)) {
            return $handlers->fromApi($field->type, $value);
        }

        // Return as-is (already a PHP object or basic type)
        return $value;
    }

    /**
     * Set a field value.
     *
     * @param string $fieldName The field name
     * @param mixed $value The field value
     * @return void
     */
    public function set(string $fieldName, mixed $value): void
    {
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
            // Get field metadata to determine type
            $field = $this->definition->getField($fieldName);

            if (!$field) {
                // Unknown field, pass through as-is
                $result[$fieldName] = $value;
                continue;
            }

            // Check if we have a handler for this field type
            if ($handlers->hasHandler($field->type)) {
                // Transform PHP object to API array format
                $result[$fieldName] = $handlers->toApi($field->type, $value);
            } else {
                // No handler, pass through as-is
                $result[$fieldName] = $value;
            }
        }

        return $result;
    }

    /**
     * Create a DynamicEntity from an array.
     *
     * @param array<string, mixed> $data The data array
     * @param EntityDefinition $definition The entity definition
     * @return self
     */
    public static function fromArray(array $data, EntityDefinition $definition): self
    {
        return new self($definition, $data);
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
    private static function getHandlerRegistry(): FieldHandlerRegistry
    {
        if (self::$handlerRegistry === null) {
            self::$handlerRegistry = new FieldHandlerRegistry();
        }

        return self::$handlerRegistry;
    }
}
