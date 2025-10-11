<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

use Factorial\TwentyCrm\Enums\FieldType;

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
     * @throws \InvalidArgumentException If field type is unknown
     */
    public static function fromArray(array $data): FieldMetadata
    {
        $typeString = $data['type'] ?? 'TEXT';
        $type = FieldType::tryFrom($typeString);

        if ($type === null) {
            throw new \InvalidArgumentException("Unknown field type: {$typeString}");
        }

        // Handle SELECT and MULTI_SELECT fields with enum options
        if ($type === FieldType::SELECT || $type === FieldType::MULTI_SELECT) {
            return self::createSelectField($data, $type);
        }

        // For now, return a generic field for other types
        // In the future, we can add more specific field classes
        return self::createGenericField($data, $type);
    }

    /**
     * Create a SelectField instance.
     *
     * @param array $data
     * @param FieldType $type
     * @return SelectField
     */
    private static function createSelectField(array $data, FieldType $type): SelectField
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
            type: $type,
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
     * @param FieldType $type
     * @return FieldMetadata
     */
    private static function createGenericField(array $data, FieldType $type): FieldMetadata
    {
        return new class (
            $data['id'] ?? '',
            $data['name'] ?? '',
            $type,
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
