<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\Enums\FieldType;

use Factorial\TwentyCrm\DTO\Address;

/**
 * Handler for ADDRESS field type.
 *
 * Transforms between Twenty CRM address object format and Address objects.
 *
 * API Format:
 * ```
 * [
 *     'addressStreet1' => '123 Main St',
 *     'addressStreet2' => 'Suite 100',
 *     'addressCity' => 'San Francisco',
 *     'addressState' => 'CA',
 *     'addressPostCode' => '94105',
 *     'addressCountry' => 'USA',
 *     'addressLat' => 37.7749,
 *     'addressLng' => -122.4194
 * ]
 * ```
 */
class AddressFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?Address
    {
        if (empty($data)) {
            return null;
        }

        return Address::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Address) {
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
        return '?' . Address::class;
    }

    public function getFieldType(): FieldType
    {
        return FieldType::ADDRESS;
    }
}
