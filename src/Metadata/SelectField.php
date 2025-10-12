<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Represents a SELECT field (single-choice enum) in Twenty CRM.
 */
class SelectField extends FieldMetadata
{
    /**
     * @param string $id
     * @param string $name
     * @param FieldType $type
     * @param string $label
     * @param string $objectMetadataId
     * @param bool $isNullable
     * @param EnumOption[] $options Available enum options
     * @param string|null $description
     * @param string|null $icon
     * @param mixed $defaultValue
     * @param bool $isCustom
     * @param bool $isActive
     * @param bool $isSystem
     */
    public function __construct(
        string $id,
        string $name,
        FieldType $type,
        string $label,
        string $objectMetadataId,
        bool $isNullable,
        private readonly array $options,
        ?string $description = null,
        ?string $icon = null,
        mixed $defaultValue = null,
        bool $isCustom = false,
        bool $isActive = true,
        bool $isSystem = false,
    ) {
        parent::__construct(
            $id,
            $name,
            $type,
            $label,
            $objectMetadataId,
            $isNullable,
            $description,
            $icon,
            $defaultValue,
            $isCustom,
            $isActive,
            $isSystem
        );
    }

    /**
     * Get all enum options.
     *
     * @return EnumOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get all valid enum values.
     *
     * @return string[]
     */
    public function getValidValues(): array
    {
        return array_map(fn (EnumOption $option) => $option->value, $this->options);
    }

    /**
     * Get options as an associative array [value => label].
     *
     * @return array<string, string>
     */
    public function getOptionsMap(): array
    {
        $map = [];
        foreach ($this->options as $option) {
            $map[$option->value] = $option->label;
        }

        return $map;
    }

    /**
     * Check if a value is valid for this field.
     *
     * @param string $value
     * @return bool
     */
    public function isValidValue(string $value): bool
    {
        return in_array($value, $this->getValidValues(), true);
    }

    /**
     * Get the label for a given value.
     *
     * @param string $value
     * @return string|null
     */
    public function getLabelForValue(string $value): ?string
    {
        foreach ($this->options as $option) {
            if ($option->value === $value) {
                return $option->label;
            }
        }

        return null;
    }

    /**
     * Get an enum option by its value.
     *
     * @param string $value
     * @return EnumOption|null
     */
    public function getOptionByValue(string $value): ?EnumOption
    {
        foreach ($this->options as $option) {
            if ($option->value === $value) {
                return $option;
            }
        }

        return null;
    }
}
