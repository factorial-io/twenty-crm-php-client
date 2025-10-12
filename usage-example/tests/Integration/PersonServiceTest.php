<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\EmailCollection;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\DTO\Link;
use Factorial\TwentyCrm\DTO\LinkCollection;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Person;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class PersonServiceTest extends IntegrationTestCase
{
    public function testCreatePerson(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();
        $person = $this->getPersonService()->createInstance();

        // Set basic fields
        $person->setEmails(new EmailCollection(primaryEmail: $email));
        $person->setName(new Name(
            firstName: $this->generateTestName('Person'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Test Engineer');

        // Set phone
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '15551234567',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $created = $this->getPersonService()->create($person);

        $this->assertNotNull($created->getId());
        $this->assertEquals($email, $created->getEmails()->getPrimaryEmail());
        $this->assertEquals($person->getName()->getFirstName(), $created->getName()->getFirstName());
        $this->assertEquals($person->getName()->getLastName(), $created->getName()->getLastName());
        $this->assertEquals($person->getJobTitle(), $created->getJobTitle());
        $this->assertStringContainsString('5551234567', $created->getPhones()->getPrimaryPhone()->getNumber());

        // Track for cleanup
        $this->trackResource('person', $created->getId());
    }

    public function testGetPersonById(): void
    {
        $this->requireClient();

        // Create a person first with full phone details
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('GetById'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Senior Developer');
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '9876543210',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Get by ID
        $retrieved = $this->getPersonService()->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertEquals($created->getId(), $retrieved->getId());
        $this->assertEquals($created->getEmails()->getPrimaryEmail(), $retrieved->getEmails()->getPrimaryEmail());
        $this->assertEquals($created->getName()->getFirstName(), $retrieved->getName()->getFirstName());
        $this->assertEquals($created->getName()->getLastName(), $retrieved->getName()->getLastName());
        $this->assertEquals($created->getJobTitle(), $retrieved->getJobTitle());

        // Verify phone details
        $this->assertNotNull($retrieved->getPhones());
        $retrievedPhone = $retrieved->getPhones()->getPrimaryPhone();
        $this->assertNotNull($retrievedPhone);
        $this->assertStringContainsString('9876543210', $retrievedPhone->getNumber());
        $this->assertEquals('US', $retrievedPhone->getCountryCode());
        $this->assertEquals('+1', $retrievedPhone->getCallingCode());
    }

    public function testGetNonExistentPersonReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->getPersonService()->getById('non-existent-id-12345');

        $this->assertNull($result);
    }

    public function testUpdatePerson(): void
    {
        $this->requireClient();

        // Create a person with comprehensive data including phone
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('Update'),
            lastName: 'Original'
        ));
        $person->setJobTitle('Junior Developer');
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '5551112222',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Verify initial values
        $this->assertEquals('Original', $created->getName()->getLastName());
        $this->assertEquals('Junior Developer', $created->getJobTitle());
        $this->assertStringContainsString('5551112222', $created->getPhones()->getPrimaryPhone()->getNumber());

        // Update multiple fields including phone
        $created->setJobTitle('Senior Engineer');
        $created->setName(new Name(
            firstName: $created->getName()->getFirstName(),
            lastName: 'Updated'
        ));

        $created->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '5559998888',
                countryCode: 'DE',
                callingCode: '+49'
            )
        ));

        $updated = $this->getPersonService()->update($created);

        // Verify updates
        $this->assertEquals('Senior Engineer', $updated->getJobTitle());
        $this->assertEquals('Updated', $updated->getName()->getLastName());
        $this->assertEquals($created->getId(), $updated->getId());
        $this->assertEquals($created->getEmails()->getPrimaryEmail(), $updated->getEmails()->getPrimaryEmail());
        $this->assertEquals($created->getName()->getFirstName(), $updated->getName()->getFirstName());

        // Verify phone update
        $this->assertNotNull($updated->getPhones());
        $updatedPhone = $updated->getPhones()->getPrimaryPhone();
        $this->assertNotNull($updatedPhone);
        $this->assertStringContainsString('5559998888', $updatedPhone->getNumber());
        $this->assertEquals('DE', $updatedPhone->getCountryCode());
        $this->assertEquals('+49', $updatedPhone->getCallingCode());
    }

    public function testDeletePerson(): void
    {
        $this->requireClient();

        // Create a person with full data
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('Delete'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Temporary Position');

        $created = $this->getPersonService()->create($person);
        $personId = $created->getId();

        // Verify it was created
        $this->assertNotNull($personId);
        $this->assertEquals('Temporary Position', $created->getJobTitle());

        // Delete it
        $result = $this->getPersonService()->delete($personId);
        $this->assertTrue($result);

        // Verify it's gone
        $retrieved = $this->getPersonService()->getById($personId);
        $this->assertNull($retrieved);
    }

    public function testDeleteNonExistentPersonReturnsFalse(): void
    {
        $this->requireClient();

        $result = $this->getPersonService()->delete('non-existent-id-12345');

        $this->assertFalse($result);
    }

    public function testFindPersonsByEmail(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();

        // Create a person with specific email and comprehensive data including phone
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $email));
        $person->setName(new Name(
            firstName: $this->generateTestName('Find'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Data Analyst');
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '123456789',
                countryCode: 'FR',
                callingCode: '+33'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Search by email using FilterBuilder
        $filter = FilterBuilder::create()
            ->equals('emails.primaryEmail', $email)
            ->build();
        $results = $this->getPersonService()->find($filter, new SearchOptions());

        $this->assertGreaterThan(0, $results->count());
        $persons = $results->getEntities();
        $this->assertEquals($email, $persons[0]->getEmails()->getPrimaryEmail());
        $this->assertEquals('Data Analyst', $persons[0]->getJobTitle());

        // Verify phone was stored correctly
        $this->assertNotNull($persons[0]->getPhones());
        $foundPhone = $persons[0]->getPhones()->getPrimaryPhone();
        $this->assertNotNull($foundPhone);
        $this->assertStringContainsString('123456789', $foundPhone->getNumber());
        $this->assertEquals('FR', $foundPhone->getCountryCode());
        $this->assertEquals('+33', $foundPhone->getCallingCode());
    }

    public function testFindByEmail(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();

        // Create a person with full profile
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $email));
        $person->setName(new Name(
            firstName: $this->generateTestName('FindEmail'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Product Manager');

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Find by email
        $filter = FilterBuilder::create()
            ->equals('emails.primaryEmail',$email)
            ->build();
        $results = $this->getPersonService()->find($filter, new SearchOptions(limit: 1));

        $this->assertGreaterThan(0, $results->count());
        $found = $results->first();

        $this->assertNotNull($found);
        $this->assertEquals($email, $found->getEmails()->getPrimaryEmail());
        $this->assertEquals('Product Manager', $found->getJobTitle());
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $this->requireClient();

        $filter = FilterBuilder::create()
            ->equals('emails.primaryEmail','nonexistent-' . $this->generateTestEmail())
            ->build();
        $results = $this->getPersonService()->find($filter, new SearchOptions(limit: 1));

        $this->assertEquals(0, $results->count());
    }

    public function testFindWithSearchOptions(): void
    {
        $this->requireClient();

        // Create multiple test persons with varied data
        $prefix = $this->generateTestName('Options');
        $jobTitles = ['Developer', 'Designer', 'Manager'];

        for ($i = 0; $i < 3; $i++) {
            $person = $this->getPersonService()->createInstance();
            $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
            $person->setName(new Name(
                firstName: "{$prefix}_{$i}",
                lastName: 'Test'
            ));
            $person->setJobTitle($jobTitles[$i]);

            $created = $this->getPersonService()->create($person);
            $this->trackResource('person', $created->getId());

            // Verify creation
            $this->assertEquals($jobTitles[$i], $created->getJobTitle());
        }

        // Search with limit
        $filter = new CustomFilter(null);
        $options = new SearchOptions(limit: 2);
        $results = $this->getPersonService()->find($filter, $options);

        $this->assertLessThanOrEqual(2, $results->count());
    }

    public function testBatchUpsert(): void
    {
        $this->requireClient();

        // Batch upsert endpoint may not be available or may require different format
        $this->markTestSkipped('Batch upsert endpoint requires further investigation');

        $persons = [];
        for ($i = 0; $i < 3; $i++) {
            $person = $this->getPersonService()->createInstance();
            $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
            $person->setName(new Name(
                firstName: $this->generateTestName("Batch_{$i}"),
                lastName: 'Test'
            ));
            $persons[] = $person;
        }

        $result = $this->getPersonService()->batchUpsert($persons);

        $this->assertGreaterThan(0, $result->count());

        // Track all for cleanup
        foreach ($result->getEntities() as $person) {
            if ($person->getId()) {
                $this->trackResource('person', $person->getId());
            }
        }
    }

    public function testCreatePersonWithMobilePhonesAndLinks(): void
    {
        $this->requireClient();

        // Create comprehensive person with mobile phones and social links
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('FullPerson'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Senior Developer');

        $person->setMobilePhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '9876543210',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $person->setLinkedinLink(new LinkCollection(
            primaryLink: new Link(
                url: 'https://linkedin.com/in/testuser',
                label: 'Test User'
            )
        ));

        $person->setXLink(new LinkCollection(
            primaryLink: new Link(
                url: 'https://x.com/testuser',
                label: '@testuser'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Verify all fields were created
        $this->assertNotNull($created->getId());
        $this->assertEquals($person->getEmails()->getPrimaryEmail(), $created->getEmails()->getPrimaryEmail());
        $this->assertEquals('Senior Developer', $created->getJobTitle());

        // Verify mobile phones
        $this->assertNotNull($created->getMobilePhones());
        $createdMobile = $created->getMobilePhones()->getPrimaryPhone();
        $this->assertNotNull($createdMobile);
        $this->assertStringContainsString('9876543210', $createdMobile->getNumber());
        $this->assertEquals('US', $createdMobile->getCountryCode());
        $this->assertEquals('+1', $createdMobile->getCallingCode());

        // Verify LinkedIn link
        $this->assertNotNull($created->getLinkedinLink());
        $createdLinkedIn = $created->getLinkedinLink()->getPrimaryLink();
        $this->assertNotNull($createdLinkedIn);
        $this->assertEquals('https://linkedin.com/in/testuser', $createdLinkedIn->getUrl());
        $this->assertEquals('Test User', $createdLinkedIn->getLabel());

        // Verify X link
        $this->assertNotNull($created->getXLink());
        $createdX = $created->getXLink()->getPrimaryLink();
        $this->assertNotNull($createdX);
        $this->assertEquals('https://x.com/testuser', $createdX->getUrl());
        $this->assertEquals('@testuser', $createdX->getLabel());
    }
}
