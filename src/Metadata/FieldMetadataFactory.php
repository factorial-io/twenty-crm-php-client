<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

/**
 * Factory for creating FieldMetadata instances from API responses.
 */
class FieldMetadataFactory
{
    /**
     * Create a FieldMetadata instance from an API response array.
     *
     * @param array $data The field data from the API
     * @return FieldMetadata
     */
    public static function fromArray(array $data): FieldMetadata
    {
        $type = $data['type'] ?? 'TEXT';

        // Handle SELECT and MULTI_SELECT fields with enum options
        if (in_array($type, ['SELECT', 'MULTI_SELECT'], true)) {
            return self::createSelectField($data);
        }

        // For now, return a generic field for other types
        // In the future, we can add more specific field classes
        return self::createGenericField($data);
    }

    /**
     * Create a SelectField instance.
     *
     * @param array $data
     * @return SelectField
     */
    private static function createSelectField(array $data): SelectField
    {
        $options = [];
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $optionData) {
                $options[] = EnumOption::fromArray($optionData);
            }
        }

        return new SelectField(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            type: $data['type'] ?? 'SELECT',
            label: $data['label'] ?? '',
            objectMetadataId: $data['objectMetadataId'] ?? '',
            isNullable: $data['isNullable'] ?? true,
            options: $options,
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            defaultValue: $data['defaultValue'] ?? null,
            isCustom: $data['isCustom'] ?? false,
            isActive: $data['isActive'] ?? true,
            isSystem: $data['isSystem'] ?? false,
        );
    }

    /**
     * Create a generic FieldMetadata instance.
     *
     * For field types that don't have specialized classes yet,
     * we create an anonymous class extending FieldMetadata.
     *
     * @param array $data
     * @return FieldMetadata
     */
    private static function createGenericField(array $data): FieldMetadata
    {
        return new class (
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? 'TEXT',
            $data['label'] ?? '',
            $data['objectMetadataId'] ?? '',
            $data['isNullable'] ?? true,
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['defaultValue'] ?? null,
            $data['isCustom'] ?? false,
            $data['isActive'] ?? true,
            $data['isSystem'] ?? false
        ) extends FieldMetadata {
        };
    }
}
