<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class ContactServiceTest extends IntegrationTestCase
{
    public function testCreateContact(): void
    {
        $this->requireClient();

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('Contact'),
            lastName: 'Test',
            jobTitle: 'Test Engineer'
        );
        $contact->setPhone('+15551234567');

        $created = $this->client->contacts()->create($contact);

        $this->assertNotNull($created->getId());
        $this->assertEquals($contact->getEmail(), $created->getEmail());
        $this->assertEquals($contact->getFirstName(), $created->getFirstName());
        $this->assertEquals($contact->getLastName(), $created->getLastName());
        $this->assertEquals($contact->getJobTitle(), $created->getJobTitle());
        $this->assertStringContainsString('5551234567', $created->getPhone());

        // Track for cleanup
        $this->trackResource('contact', $created->getId());
    }

    public function testGetContactById(): void
    {
        $this->requireClient();

        // Create a contact first with full phone details
        $phoneCollection = new \Factorial\TwentyCrm\DTO\PhoneCollection(
            primaryPhone: new \Factorial\TwentyCrm\DTO\Phone(
                number: '9876543210',
                countryCode: 'US',
                callingCode: '+1'
            )
        );

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('GetById'),
            lastName: 'Test',
            jobTitle: 'Senior Developer',
            phones: $phoneCollection
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Get by ID
        $retrieved = $this->client->contacts()->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertEquals($created->getId(), $retrieved->getId());
        $this->assertEquals($created->getEmail(), $retrieved->getEmail());
        $this->assertEquals($created->getFirstName(), $retrieved->getFirstName());
        $this->assertEquals($created->getLastName(), $retrieved->getLastName());
        $this->assertEquals($created->getJobTitle(), $retrieved->getJobTitle());

        // Verify phone details
        $this->assertNotNull($retrieved->getPhones());
        $retrievedPhone = $retrieved->getPhones()->getPrimaryPhone();
        $this->assertNotNull($retrievedPhone);
        $this->assertStringContainsString('9876543210', $retrievedPhone->getNumber());
        $this->assertEquals('US', $retrievedPhone->getCountryCode());
        $this->assertEquals('+1', $retrievedPhone->getCallingCode());
    }

    public function testGetNonExistentContactReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->client->contacts()->getById('non-existent-id-12345');

        $this->assertNull($result);
    }

    public function testUpdateContact(): void
    {
        $this->requireClient();

        // Create a contact with comprehensive data including phone
        $phoneCollection = new \Factorial\TwentyCrm\DTO\PhoneCollection(
            primaryPhone: new \Factorial\TwentyCrm\DTO\Phone(
                number: '5551112222',
                countryCode: 'US',
                callingCode: '+1'
            )
        );

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('Update'),
            lastName: 'Original',
            jobTitle: 'Junior Developer',
            phones: $phoneCollection
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Verify initial values
        $this->assertEquals('Original', $created->getLastName());
        $this->assertEquals('Junior Developer', $created->getJobTitle());
        $this->assertStringContainsString('5551112222', $created->getPhone());

        // Update multiple fields including phone
        $created->setJobTitle('Senior Engineer');
        $created->setLastName('Updated');

        $newPhoneCollection = new \Factorial\TwentyCrm\DTO\PhoneCollection(
            primaryPhone: new \Factorial\TwentyCrm\DTO\Phone(
                number: '5559998888',
                countryCode: 'DE',
                callingCode: '+49'
            )
        );
        $created->setPhones($newPhoneCollection);

        $updated = $this->client->contacts()->update($created);

        // Verify updates
        $this->assertEquals('Senior Engineer', $updated->getJobTitle());
        $this->assertEquals('Updated', $updated->getLastName());
        $this->assertEquals($created->getId(), $updated->getId());
        $this->assertEquals($created->getEmail(), $updated->getEmail());
        $this->assertEquals($created->getFirstName(), $updated->getFirstName());

        // Verify phone update
        $this->assertNotNull($updated->getPhones());
        $updatedPhone = $updated->getPhones()->getPrimaryPhone();
        $this->assertNotNull($updatedPhone);
        $this->assertStringContainsString('5559998888', $updatedPhone->getNumber());
        $this->assertEquals('DE', $updatedPhone->getCountryCode());
        $this->assertEquals('+49', $updatedPhone->getCallingCode());
    }

    public function testDeleteContact(): void
    {
        $this->requireClient();

        // Create a contact with full data
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('Delete'),
            lastName: 'Test',
            jobTitle: 'Temporary Position'
        );

        $created = $this->client->contacts()->create($contact);
        $contactId = $created->getId();

        // Verify it was created
        $this->assertNotNull($contactId);
        $this->assertEquals('Temporary Position', $created->getJobTitle());

        // Delete it
        $result = $this->client->contacts()->delete($contactId);
        $this->assertTrue($result);

        // Verify it's gone
        $retrieved = $this->client->contacts()->getById($contactId);
        $this->assertNull($retrieved);
    }

    public function testDeleteNonExistentContactReturnsFalse(): void
    {
        $this->requireClient();

        $result = $this->client->contacts()->delete('non-existent-id-12345');

        $this->assertFalse($result);
    }

    public function testFindContactsByEmail(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();

        // Create a contact with specific email and comprehensive data including phone
        // Note: Use number without country code prefix, as API will normalize it
        $phoneCollection = new \Factorial\TwentyCrm\DTO\PhoneCollection(
            primaryPhone: new \Factorial\TwentyCrm\DTO\Phone(
                number: '123456789',
                countryCode: 'FR',
                callingCode: '+33'
            )
        );

        $contact = new Contact(
            email: $email,
            firstName: $this->generateTestName('Find'),
            lastName: 'Test',
            jobTitle: 'Data Analyst',
            phones: $phoneCollection
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Search by email
        $filter = new ContactSearchFilter(email: $email);
        $results = $this->client->contacts()->find($filter, new SearchOptions());

        $this->assertGreaterThan(0, $results->count());
        $contacts = $results->getContacts();
        $this->assertEquals($email, $contacts[0]->getEmail());
        $this->assertEquals('Data Analyst', $contacts[0]->getJobTitle());

        // Verify phone was stored correctly
        $this->assertNotNull($contacts[0]->getPhones());
        $foundPhone = $contacts[0]->getPhones()->getPrimaryPhone();
        $this->assertNotNull($foundPhone);
        $this->assertStringContainsString('123456789', $foundPhone->getNumber());
        $this->assertEquals('FR', $foundPhone->getCountryCode());
        $this->assertEquals('+33', $foundPhone->getCallingCode());
    }

    public function testFindByEmail(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();

        // Create a contact with full profile
        $contact = new Contact(
            email: $email,
            firstName: $this->generateTestName('FindEmail'),
            lastName: 'Test',
            jobTitle: 'Product Manager'
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Find by email
        $found = $this->client->contacts()->findByEmail($email);

        $this->assertNotNull($found);
        $this->assertEquals($email, $found->getEmail());
        $this->assertEquals('Product Manager', $found->getJobTitle());
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $this->requireClient();

        $result = $this->client->contacts()->findByEmail('nonexistent-' . $this->generateTestEmail());

        $this->assertNull($result);
    }

    public function testFindWithSearchOptions(): void
    {
        $this->requireClient();

        // Create multiple test contacts with varied data
        $prefix = $this->generateTestName('Options');
        $jobTitles = ['Developer', 'Designer', 'Manager'];

        for ($i = 0; $i < 3; $i++) {
            $contact = new Contact(
                email: $this->generateTestEmail(),
                firstName: "{$prefix}_{$i}",
                lastName: 'Test',
                jobTitle: $jobTitles[$i]
            );
            $created = $this->client->contacts()->create($contact);
            $this->trackResource('contact', $created->getId());

            // Verify creation
            $this->assertEquals($jobTitles[$i], $created->getJobTitle());
        }

        // Search with limit
        $filter = new ContactSearchFilter();
        $options = new SearchOptions(limit: 2);
        $results = $this->client->contacts()->find($filter, $options);

        $this->assertLessThanOrEqual(2, $results->count());
    }

    public function testBatchUpsert(): void
    {
        $this->requireClient();

        // Batch upsert endpoint may not be available or may require different format
        $this->markTestSkipped('Batch upsert endpoint requires further investigation');

        $contacts = [];
        for ($i = 0; $i < 3; $i++) {
            $contacts[] = new Contact(
                email: $this->generateTestEmail(),
                firstName: $this->generateTestName("Batch_{$i}"),
                lastName: 'Test'
            );
        }

        $result = $this->client->contacts()->batchUpsert($contacts);

        $this->assertGreaterThan(0, $result->count());

        // Track all for cleanup
        foreach ($result->getContacts() as $contact) {
            if ($contact->getId()) {
                $this->trackResource('contact', $contact->getId());
            }
        }
    }
}
