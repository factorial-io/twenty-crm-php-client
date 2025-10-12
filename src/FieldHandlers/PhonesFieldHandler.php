<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\Collection\PhoneCollection;
use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Handler for PHONES field type.
 *
 * Transforms between Twenty CRM phone array format and PhoneCollection objects.
 *
 * API Format:
 * ```
 * [
 *     'primaryPhoneNumber' => '+1234567890',
 *     'primaryPhoneCountryCode' => 'US',
 *     'primaryPhoneCallingCode' => '+1',
 *     'additionalPhones' => [
 *         ['phoneNumber' => '+9876543210', 'countryCode' => 'UK']
 *     ]
 * ]
 * ```
 */
class PhonesFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?PhoneCollection
    {
        if (empty($data)) {
            return null;
        }

        return PhoneCollection::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof PhoneCollection) {
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
        return '?' . PhoneCollection::class;
    }

    public function getFieldType(): FieldType
    {
        return FieldType::PHONES;
    }
}
