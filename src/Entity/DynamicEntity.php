<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

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
class DynamicEntity extends AbstractEntity
{
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

    // ====================================================================
    // AbstractEntity implementation - metadata access via EntityDefinition
    // ====================================================================

    /**
     * Get field metadata from EntityDefinition.
     *
     * @param string $fieldName The field name
     * @return array<string, mixed>|null Field metadata or null if not found
     */
    protected function getFieldMetadataArray(string $fieldName): ?array
    {
        $field = $this->definition->getField($fieldName);

        if (!$field) {
            return null;
        }

        $handlers = self::getHandlerRegistry();

        return [
            'type' => $field->type,
            'nullable' => $field->isNullable,
            'hasHandler' => $handlers->hasHandler($field->type),
        ];
    }

    /**
     * Map entity field name to API field name using EntityDefinition.
     *
     * @param string $fieldName Entity field name
     * @return string API field name
     */
    protected function mapFieldToApi(string $fieldName): string
    {
        return $this->definition->mapFieldToApi($fieldName);
    }

    /**
     * Map API field name to entity field name using EntityDefinition.
     *
     * @param string $apiFieldName API field name
     * @return string Entity field name
     */
    protected function mapApiToField(string $apiFieldName): string
    {
        return $this->definition->mapApiToField($apiFieldName);
    }

    // ====================================================================
    // Factory method
    // ====================================================================

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
}
