<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

/**
 * Handler for EMAILS field type.
 *
 * Transforms between Twenty CRM emails object format and simple string.
 * Extracts primary email for simpler API usage.
 *
 * API Format:
 * ```
 * [
 *     'primaryEmail' => 'john@example.com',
 *     'additionalEmails' => ['jane@example.com', 'support@example.com']
 * ]
 * ```
 *
 * PHP Format: Returns primary email as string
 */
class EmailsFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        // Extract primary email
        if (isset($data['primaryEmail']) && !empty($data['primaryEmail'])) {
            return $data['primaryEmail'];
        }

        // Fallback to first additional email
        if (isset($data['additionalEmails'][0])) {
            return $data['additionalEmails'][0];
        }

        return null;
    }

    public function toApi(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        // If it's already an array (full emails object), pass through
        if (is_array($value)) {
            return $value;
        }

        // Convert string email to emails object
        if (is_string($value)) {
            return ['primaryEmail' => $value];
        }

        return [];
    }

    public function getPhpType(): string
    {
        return '?string';
    }

    public function getFieldType(): string
    {
        return 'EMAILS';
    }
}
