<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\Enums\FieldType;
use Factorial\TwentyCrm\DTO\Name;

/**
 * Handler for FULL_NAME field type.
 *
 * Transforms between Twenty CRM name object format and Name objects.
 *
 * API Format:
 * ```
 * [
 *     'firstName' => 'John',
 *     'lastName' => 'Doe'
 * ]
 * ```
 */
class NameFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?Name
    {
        if (empty($data)) {
            return null;
        }

        return Name::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Name) {
            return $value->toArray();
        }

        // If already an array, pass through
        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    public function getPhpType(): string
    {
        return '?' . Name::class;
    }

    public function getFieldType(): FieldType
    {
        return FieldType::FULL_NAME;
    }
}
