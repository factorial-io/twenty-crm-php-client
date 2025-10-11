<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

/**
 * Constants for Twenty CRM field behavior.
 *
 * This class centralizes knowledge about which fields are auto-managed
 * by the Twenty CRM API and should not be modified by client code.
 */
class FieldConstants
{
    /**
     * Auto-managed timestamp and audit fields.
     *
     * These fields are automatically managed by the database/API and should
     * never be included in update operations, even though they may have
     * isSystem=false in the metadata.
     *
     * From PRD Section C: Field Filtering Strategy:
     * - createdAt: Auto-set on entity creation
     * - updatedAt: Auto-updated on entity modification
     * - deletedAt: Auto-set on soft delete
     * - createdBy: Auto-set to current user on creation
     *
     * @var array<string>
     */
    public const AUTO_MANAGED_FIELDS = [
        'createdAt',
        'updatedAt',
        'deletedAt',
        'createdBy',
    ];

    /**
     * Check if a field is auto-managed.
     *
     * Auto-managed fields are handled by the database/API automatically
     * and should not be set by user code.
     *
     * @param string $fieldName The field name to check
     * @return bool True if the field is auto-managed
     */
    public static function isAutoManaged(string $fieldName): bool
    {
        return in_array($fieldName, self::AUTO_MANAGED_FIELDS, true);
    }

    /**
     * Check if a field is updatable based on metadata and conventions.
     *
     * A field is updatable if:
     * 1. It is NOT marked as isSystem in the metadata
     * 2. It is NOT in the auto-managed fields list
     *
     * @param FieldMetadata $field The field metadata
     * @return bool True if the field can be updated
     */
    public static function isUpdatable(FieldMetadata $field): bool
    {
        // System fields are never updatable
        if ($field->isSystem) {
            return false;
        }

        // Auto-managed fields are never updatable
        if (self::isAutoManaged($field->name)) {
            return false;
        }

        return true;
    }

    /**
     * Filter an array of field data to only include updatable fields.
     *
     * This is a helper for filtering entity data before sending updates
     * to the API.
     *
     * @param array<string, mixed> $data The field data
     * @param array<string, FieldMetadata> $fieldDefinitions The field metadata
     * @return array<string, mixed> Filtered data with only updatable fields
     */
    public static function filterUpdatableFields(array $data, array $fieldDefinitions): array
    {
        $filtered = [];

        foreach ($data as $fieldName => $value) {
            // Skip auto-managed fields
            if (self::isAutoManaged($fieldName)) {
                continue;
            }

            // Check field metadata
            $fieldMeta = $fieldDefinitions[$fieldName] ?? null;

            // Skip if field not in definition (safety)
            if (!$fieldMeta) {
                continue;
            }

            // Skip system fields
            if ($fieldMeta->isSystem) {
                continue;
            }

            // Include this field in the update
            $filtered[$fieldName] = $value;
        }

        return $filtered;
    }
}
