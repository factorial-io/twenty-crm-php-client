<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\FieldMetadata;
use Factorial\TwentyCrm\Metadata\FieldMetadataFactory;
use Factorial\TwentyCrm\Metadata\SelectField;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Service for fetching and caching field metadata from Twenty CRM.
 */
class MetadataService
{
    /**
     * Cache of field metadata, indexed by object name and field name.
     *
     * @var array<string, array<string, FieldMetadata>>
     */
    private array $fieldCache = [];

    /**
     * Cache of all fields.
     *
     * @var FieldMetadata[]|null
     */
    private ?array $allFieldsCache = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * Get metadata for a specific field.
     *
     * @param string $objectName The object name (e.g., 'person', 'company')
     * @param string $fieldName The field name (e.g., 'leadSource')
     * @return FieldMetadata|null
     */
    public function getFieldMetadata(string $objectName, string $fieldName): ?FieldMetadata
    {
        // Check cache first
        if (isset($this->fieldCache[$objectName][$fieldName])) {
            return $this->fieldCache[$objectName][$fieldName];
        }

        // Fetch all fields if not cached
        if ($this->allFieldsCache === null) {
            $this->fetchAllFields();
        }

        // Try again from cache after fetching
        return $this->fieldCache[$objectName][$fieldName] ?? null;
    }

    /**
     * Get all fields for a specific object.
     *
     * @param string $objectName The object name (e.g., 'person', 'company')
     * @return FieldMetadata[]
     */
    public function getObjectFields(string $objectName): array
    {
        // Ensure all fields are loaded
        if ($this->allFieldsCache === null) {
            $this->fetchAllFields();
        }

        return $this->fieldCache[$objectName] ?? [];
    }

    /**
     * Get valid enum values for a SELECT field.
     *
     * @param string $objectName The object name
     * @param string $fieldName The field name
     * @return string[]
     */
    public function getEnumValues(string $objectName, string $fieldName): array
    {
        // Try to get from cache first, or fetch from API
        $field = $this->fetchFieldMetadataByObject($objectName, $fieldName);

        if ($field instanceof SelectField) {
            return $field->getValidValues();
        }

        return [];
    }

    /**
     * Check if a value is valid for an enum field.
     *
     * @param string $objectName The object name
     * @param string $fieldName The field name
     * @param string $value The value to check
     * @return bool
     */
    public function isValidEnumValue(string $objectName, string $fieldName, string $value): bool
    {
        // Try to get from cache first, or fetch from API
        $field = $this->fetchFieldMetadataByObject($objectName, $fieldName);

        if ($field instanceof SelectField) {
            return $field->isValidValue($value);
        }

        return false;
    }

    /**
     * Clear the metadata cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->fieldCache = [];
        $this->allFieldsCache = null;
    }

    /**
     * Fetch all fields from the metadata API.
     *
     * @return void
     */
    private function fetchAllFields(): void
    {
        $this->logger->debug('Fetching all field metadata');

        try {
            // The metadata endpoint is at /rest/metadata/fields
            // Note: metadata endpoints don't accept query parameters
            $response = $this->httpClient->request('GET', 'metadata/fields');

            if (isset($response['data']['fields']) && is_array($response['data']['fields'])) {
                $this->allFieldsCache = [];

                foreach ($response['data']['fields'] as $fieldData) {
                    $field = FieldMetadataFactory::fromArray($fieldData);
                    $this->allFieldsCache[] = $field;

                    // Note: We cannot cache by object name here because the flat
                    // fields endpoint doesn't include object information.
                    // Use fetchFieldMetadataByObject() for object-specific lookups.
                }

                $this->logger->debug('Field metadata loaded', [
                    'count' => count($this->allFieldsCache),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch field metadata', [
                'error' => $e->getMessage(),
            ]);
            // Log error but don't throw - allow graceful degradation
            $this->allFieldsCache = [];
        }
    }

    /**
     * Fetch field metadata by querying the objects endpoint first.
     *
     * This is an alternative approach that fetches objects to get the mapping.
     *
     * @param string $objectName
     * @param string $fieldName
     * @return FieldMetadata|null
     */
    public function fetchFieldMetadataByObject(string $objectName, string $fieldName): ?FieldMetadata
    {
        // Check cache first
        if (isset($this->fieldCache[$objectName][$fieldName])) {
            return $this->fieldCache[$objectName][$fieldName];
        }

        try {
            // Get the object metadata which includes nested fields
            // Note: metadata endpoints don't accept query parameters
            $objectsResponse = $this->httpClient->request('GET', 'metadata/objects');

            if (isset($objectsResponse['data']['objects']) && is_array($objectsResponse['data']['objects'])) {
                foreach ($objectsResponse['data']['objects'] as $object) {
                    // Match by singular or plural name
                    if (
                        ($object['nameSingular'] ?? null) === $objectName ||
                        ($object['namePlural'] ?? null) === $objectName
                    ) {
                        // Check if fields are nested in the object
                        if (isset($object['fields']) && is_array($object['fields'])) {
                            foreach ($object['fields'] as $fieldData) {
                                if (($fieldData['name'] ?? null) === $fieldName) {
                                    // Add objectMetadataId to field data
                                    $fieldData['objectMetadataId'] = $object['id'];

                                    $field = FieldMetadataFactory::fromArray($fieldData);

                                    // Cache it
                                    if (!isset($this->fieldCache[$objectName])) {
                                        $this->fieldCache[$objectName] = [];
                                    }
                                    $this->fieldCache[$objectName][$fieldName] = $field;

                                    return $field;
                                }
                            }
                        }

                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error
        }

        return null;
    }
}
