<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\DTO\EmailCollection;

/**
 * Handler for EMAILS field type.
 *
 * Transforms between Twenty CRM emails object format and EmailCollection.
 *
 * API Format:
 * ```
 * [
 *     'primaryEmail' => 'john@example.com',
 *     'additionalEmails' => ['jane@example.com', 'support@example.com']
 * ]
 * ```
 *
 * PHP Format: Returns EmailCollection object
 */
class EmailsFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?EmailCollection
    {
        if (empty($data)) {
            return null;
        }

        return EmailCollection::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        // If it's an EmailCollection, convert to array
        if ($value instanceof EmailCollection) {
            return $value->toArray();
        }

        // If it's already an array (full emails object), pass through
        if (is_array($value)) {
            return $value;
        }

        // Convert string email to emails object (backward compatibility)
        if (is_string($value) && $value !== '') {
            return ['primaryEmail' => $value];
        }

        return [];
    }

    public function getPhpType(): string
    {
        return '?' . EmailCollection::class;
    }

    public function getFieldType(): string
    {
        return 'EMAILS';
    }
}
