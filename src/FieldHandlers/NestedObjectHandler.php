<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

/**
 * Interface for handling complex nested field transformations.
 *
 * Field handlers transform between Twenty CRM API format and PHP objects.
 * This allows generated entities to use typed collections (PhoneCollection,
 * LinkCollection) instead of raw arrays.
 *
 * Example:
 * ```php
 * // API format (from Twenty CRM)
 * $apiData = [
 *     'primaryPhoneNumber' => '+1234567890',
 *     'primaryPhoneCountryCode' => 'US',
 *     'additionalPhones' => [...]
 * ];
 *
 * // Transform to PHP object
 * $phones = $handler->fromApi($apiData);  // Returns PhoneCollection
 * $phones->getPrimaryNumber();  // '+1234567890'
 *
 * // Transform back to API format
 * $apiData = $handler->toApi($phones);  // Array for API request
 * ```
 */
interface NestedObjectHandler
{
    /**
     * Transform API data to PHP object.
     *
     * Converts raw array data from Twenty CRM API into typed PHP objects
     * like PhoneCollection, LinkCollection, etc.
     *
     * @param array $data Raw array data from API
     * @return mixed Typed PHP object (e.g., PhoneCollection, LinkCollection)
     */
    public function fromApi(array $data): mixed;

    /**
     * Transform PHP object to API format.
     *
     * Converts typed PHP objects back to array format suitable for
     * Twenty CRM API requests.
     *
     * @param mixed $value PHP object (e.g., PhoneCollection) or array
     * @return array Array data for API request
     */
    public function toApi(mixed $value): array;

    /**
     * Get the PHP type returned by fromApi().
     *
     * Used by code generator to determine return types for getters.
     *
     * @return string Fully qualified class name (e.g., 'Factorial\TwentyCrm\DTO\PhoneCollection')
     */
    public function getPhpType(): string;

    /**
     * Get the Twenty CRM field type this handler supports.
     *
     * Used to register handlers for specific field types.
     *
     * @return string Twenty field type (e.g., 'PHONES', 'LINKS', 'EMAILS')
     */
    public function getFieldType(): string;
}
