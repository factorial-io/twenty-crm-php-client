<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\Enums\FieldType;
use Factorial\TwentyCrm\DTO\Currency;

/**
 * Handler for CURRENCY field type.
 *
 * Transforms between Twenty CRM currency format and Currency object.
 *
 * API Format:
 * ```
 * [
 *     'amountMicros' => 50000000000,  // $50,000 in micros
 *     'currencyCode' => 'USD'
 * ]
 * ```
 *
 * PHP Format: Returns Currency object
 */
class CurrencyFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?Currency
    {
        if (empty($data)) {
            return null;
        }

        // If amountMicros is not set or null, return null
        if (!isset($data['amountMicros'])) {
            return null;
        }

        return Currency::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        // If it's a Currency object, convert to array
        if ($value instanceof Currency) {
            return $value->toArray();
        }

        // If it's already an array (API format), pass through
        if (is_array($value)) {
            return $value;
        }

        // If it's a float/int, treat as standard amount in USD
        if (is_numeric($value)) {
            return Currency::fromAmount((float) $value, 'USD')->toArray();
        }

        return [];
    }

    public function getPhpType(): string
    {
        return '?' . Currency::class;
    }

    public function getFieldType(): FieldType
    {
        return FieldType::CURRENCY;
    }
}
