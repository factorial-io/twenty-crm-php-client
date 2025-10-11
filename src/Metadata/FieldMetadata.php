<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Abstract base class for field metadata.
 *
 * Represents metadata about a field in a Twenty CRM object.
 */
abstract class FieldMetadata
{
    /**
     * @param string $id The field ID
     * @param string $name The field name (API key)
     * @param FieldType $type The field type
     * @param string $label The human-readable label
     * @param string $objectMetadataId The ID of the object this field belongs to
     * @param bool $isNullable Whether the field can be null
     * @param string|null $description Optional field description
     * @param string|null $icon Optional icon identifier
     * @param mixed $defaultValue Optional default value
     * @param bool $isCustom Whether this is a custom field
     * @param bool $isActive Whether the field is active
     * @param bool $isSystem Whether this is a system field
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly FieldType $type,
        public readonly string $label,
        public readonly string $objectMetadataId,
        public readonly bool $isNullable,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly mixed $defaultValue = null,
        public readonly bool $isCustom = false,
        public readonly bool $isActive = true,
        public readonly bool $isSystem = false,
    ) {
    }

    /**
     * Check if this field is required (not nullable).
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return !$this->isNullable;
    }

    /**
     * Check if this is a relation field.
     *
     * @return bool
     */
    public function isRelation(): bool
    {
        return $this->type->isRelation();
    }
}
