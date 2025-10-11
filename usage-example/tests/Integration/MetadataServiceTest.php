<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\Metadata\SelectField;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration tests for the MetadataService.
 *
 * @group integration
 */
class MetadataServiceTest extends IntegrationTestCase
{
    /**
     * Test fetching field metadata for a SELECT field.
     */
    public function testFetchSelectFieldMetadata(): void
    {
        $this->requireClient();

        // Fetch leadSource field metadata for person object
        $field = $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');

        $this->assertInstanceOf(SelectField::class, $field);
        $this->assertSame('leadSource', $field->name);
        $this->assertSame('SELECT', $field->type);
        $this->assertSame('Lead Source', $field->label);
        $this->assertTrue($field->isNullable);

        // Check that options are available
        $options = $field->getOptions();
        $this->assertNotEmpty($options);
        $this->assertGreaterThan(0, count($options));

        // Each option should have required properties
        foreach ($options as $option) {
            $this->assertNotEmpty($option->value);
            $this->assertNotEmpty($option->label);
            $this->assertNotEmpty($option->color);
            $this->assertIsInt($option->position);
        }
    }

    /**
     * Test getting valid enum values.
     */
    public function testGetEnumValues(): void
    {
        $this->requireClient();

        $validValues = $this->client->metadata()->getEnumValues('person', 'leadSource');

        $this->assertIsArray($validValues);
        $this->assertNotEmpty($validValues);

        // All values should be non-empty strings
        foreach ($validValues as $value) {
            $this->assertIsString($value);
            $this->assertNotEmpty($value);
        }
    }

    /**
     * Test validating enum values.
     */
    public function testIsValidEnumValue(): void
    {
        $this->requireClient();

        // Get one valid value
        $validValues = $this->client->metadata()->getEnumValues('person', 'leadSource');
        $this->assertNotEmpty($validValues);

        $validValue = $validValues[0];

        // Test that a valid value is accepted
        $this->assertTrue(
            $this->client->metadata()->isValidEnumValue('person', 'leadSource', $validValue)
        );

        // Test that an invalid value is rejected
        $this->assertFalse(
            $this->client->metadata()->isValidEnumValue('person', 'leadSource', 'INVALID_VALUE')
        );
    }

    /**
     * Test metadata caching.
     */
    public function testMetadataCaching(): void
    {
        $this->requireClient();

        // First call - fetches from API
        $field1 = $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');
        $this->assertInstanceOf(SelectField::class, $field1);

        // Second call - should use cache
        $field2 = $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');
        $this->assertInstanceOf(SelectField::class, $field2);

        // Should return the same instance from cache
        $this->assertSame($field1, $field2);
    }

    /**
     * Test getting all fields for an object.
     */
    public function testGetObjectFields(): void
    {
        $this->requireClient();

        // First, fetch one field to populate cache
        $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');

        // Now get all cached fields for person
        $fields = $this->client->metadata()->getObjectFields('person');

        $this->assertIsArray($fields);
        $this->assertArrayHasKey('leadSource', $fields);
        $this->assertInstanceOf(SelectField::class, $fields['leadSource']);
    }

    /**
     * Test clearing the metadata cache.
     */
    public function testClearCache(): void
    {
        $this->requireClient();

        // Fetch field to populate cache
        $field1 = $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');
        $this->assertInstanceOf(SelectField::class, $field1);

        // Clear cache
        $this->client->metadata()->clearCache();

        // Fetch again - should create new instance
        $field2 = $this->client->metadata()->fetchFieldMetadataByObject('person', 'leadSource');
        $this->assertInstanceOf(SelectField::class, $field2);

        // Should not be the same instance
        $this->assertNotSame($field1, $field2);
    }

    /**
     * Test that non-existent field returns null.
     */
    public function testFetchNonExistentField(): void
    {
        $this->requireClient();

        $field = $this->client->metadata()->fetchFieldMetadataByObject('person', 'nonExistentField');

        $this->assertNull($field);
    }

    /**
     * Test that non-existent object returns null.
     */
    public function testFetchFieldFromNonExistentObject(): void
    {
        $this->requireClient();

        $field = $this->client->metadata()->fetchFieldMetadataByObject('nonExistentObject', 'someField');

        $this->assertNull($field);
    }
}
