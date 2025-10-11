<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

/**
 * Represents an option in a SELECT or MULTI_SELECT field.
 */
class EnumOption
{
    /**
     * @param string $value The enum value (e.g., "LINKEDIN", "OPTION_1")
     * @param string $label The human-readable label (e.g., "LinkedIn")
     * @param string $color The color code for UI display
     * @param int $position The display order position
     */
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly string $color,
        public readonly int $position,
    ) {
    }

    /**
     * Create EnumOption from API response array.
     *
     * @param array $data The API response data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'] ?? '',
            label: $data['label'] ?? '',
            color: $data['color'] ?? '',
            position: $data['position'] ?? 0,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'color' => $this->color,
            'position' => $this->position,
        ];
    }
}
