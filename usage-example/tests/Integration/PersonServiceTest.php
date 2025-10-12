<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Email;
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

/**
 * Integration tests for generated PersonService.
 *
 * Tests the generated Person entity and PersonService from
 * usage-example/src/TwentyCrm/Entity/ and usage-example/src/TwentyCrm/Service/
 */
class PersonServiceTest extends IntegrationTestCase
{
    public function testCreatePerson(): void
    {
        $this->requireClient();

        // Create Person using generated entity
        $person = $this->personService->createInstance();

        // Set name
        $person->setName(new Name(
            firstName: $this->generateTestName('Person'),
            lastName: 'Test'
        ));

        // Set email
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));

        // Set job title
        $person->setJobTitle('Test Engineer');

        // Set phone
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone('+15551234567', 'US', '+1')
        ));

        $created = $this->personService->create($person);

        $this->assertNotNull($created->getId());
        $this->assertEquals($person->getName()->getFirstName(), $created->getName()->getFirstName());
        $this->assertEquals($person->getName()->getLastName(), $created->getName()->getLastName());
        $this->assertEquals('Test Engineer', $created->getJobTitle());
        $this->assertNotNull($created->getPhones());

        // Track for cleanup
        $this->trackResource('person', $created->getId());
    }

    public function testGetPersonById(): void
    {
        $this->requireClient();

        // Create a person first
        $person = $this->personService->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('GetById'),
            lastName: 'Test'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Senior Developer');
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone('9876543210', 'US', '+1')
        ));

        $created = $this->personService->create($person);
        $this->trackResource('person', $created->getId());

        // Get by ID
        $retrieved = $this->personService->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertEquals($created->getId(), $retrieved->getId());
        $this->assertEquals('Senior Developer', $retrieved->getJobTitle());

        // Verify phone
        $this->assertNotNull($retrieved->getPhones());
        $phone = $retrieved->getPhones()->getPrimaryPhone();
        $this->assertNotNull($phone);
        $this->assertStringContainsString('9876543210', $phone->getNumber());
    }

    public function testGetNonExistentPersonReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->personService->getById('non-existent-id-12345');

        $this->assertNull($result);
    }

    public function testUpdatePerson(): void
    {
        $this->requireClient();

        // Create a person
        $person = $this->personService->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('Update'),
            lastName: 'Original'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Junior Developer');

        $created = $this->personService->create($person);
        $this->trackResource('person', $created->getId());

        // Verify initial values
        $this->assertEquals('Original', $created->getName()->getLastName());
        $this->assertEquals('Junior Developer', $created->getJobTitle());

        // Update fields
        $created->setJobTitle('Senior Engineer');
        $created->setName(new Name(
            firstName: $created->getName()->getFirstName(),
            lastName: 'Updated'
        ));

        $updated = $this->personService->update($created);

        // Verify updates
        $this->assertEquals('Senior Engineer', $updated->getJobTitle());
        $this->assertEquals('Updated', $updated->getName()->getLastName());
        $this->assertEquals($created->getId(), $updated->getId());
    }

    public function testDeletePerson(): void
    {
        $this->requireClient();

        // Create a person
        $person = $this->personService->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('Delete'),
            lastName: 'Test'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Temporary Position');

        $created = $this->personService->create($person);
        $personId = $created->getId();

        // Verify it was created
        $this->assertNotNull($personId);

        // Delete it
        $result = $this->personService->delete($personId);
        $this->assertTrue($result);

        // Verify it's gone
        $retrieved = $this->personService->getById($personId);
        $this->assertNull($retrieved);
    }

    public function testDeleteNonExistentPersonReturnsFalse(): void
    {
        $this->requireClient();

        $result = $this->personService->delete('non-existent-id-12345');

        $this->assertFalse($result);
    }

    public function testFindPersons(): void
    {
        $this->requireClient();

        $email = $this->generateTestEmail();

        // Create a person
        $person = $this->personService->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('Find'),
            lastName: 'Test'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $email
        ));
        $person->setJobTitle('Data Analyst');

        $created = $this->personService->create($person);
        $this->trackResource('person', $created->getId());

        // Search with filter
        $filter = FilterBuilder::create()
            ->equals('emails.primaryEmail', $email)
            ->build();

        $results = $this->personService->find($filter, new SearchOptions());

        $this->assertGreaterThan(0, $results->count());
        $persons = $results->getPersons();
        $this->assertEquals($email, $persons[0]->getEmails()->getPrimaryEmail());
        $this->assertEquals('Data Analyst', $persons[0]->getJobTitle());
    }

    public function testFindWithSearchOptions(): void
    {
        $this->requireClient();

        // Create multiple test persons
        $prefix = $this->generateTestName('Options');
        $jobTitles = ['Developer', 'Designer', 'Manager'];

        for ($i = 0; $i < 3; $i++) {
            $person = $this->personService->createInstance();
            $person->setName(new Name(
                firstName: "{$prefix}_{$i}",
                lastName: 'Test'
            ));
            $person->setEmails(new EmailCollection(
                primaryEmail: $this->generateTestEmail()
            ));
            $person->setJobTitle($jobTitles[$i]);

            $created = $this->personService->create($person);
            $this->trackResource('person', $created->getId());
        }

        // Search with limit
        $filter = FilterBuilder::create()->build();
        $options = new SearchOptions(limit: 2);
        $results = $this->personService->find($filter, $options);

        $this->assertLessThanOrEqual(2, $results->count());
    }

    public function testCreatePersonWithComplexFields(): void
    {
        $this->requireClient();

        // Create comprehensive person with all complex fields
        $person = $this->personService->createInstance();

        $person->setName(new Name(
            firstName: $this->generateTestName('FullPerson'),
            lastName: 'Test'
        ));

        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));

        $person->setJobTitle('Senior Developer');

        $person->setMobilePhones(new PhoneCollection(
            primaryPhone: new Phone('9876543210', 'US', '+1')
        ));

        $person->setLinkedinLink(new LinkCollection(
            primaryLink: new Link('https://linkedin.com/in/testuser', 'Test User')
        ));

        $person->setXLink(new LinkCollection(
            primaryLink: new Link('https://x.com/testuser', '@testuser')
        ));

        $created = $this->personService->create($person);
        $this->trackResource('person', $created->getId());

        // Verify all fields
        $this->assertNotNull($created->getId());
        $this->assertEquals('Senior Developer', $created->getJobTitle());

        // Verify mobile phones
        $this->assertNotNull($created->getMobilePhones());
        $mobile = $created->getMobilePhones()->getPrimaryPhone();
        $this->assertNotNull($mobile);
        $this->assertStringContainsString('9876543210', $mobile->getNumber());

        // Verify LinkedIn link
        $linkedIn = $created->getLinkedinLink();
        if ($linkedIn) {
            $link = $linkedIn->getPrimaryLink();
            if ($link) {
                $this->assertEquals('https://linkedin.com/in/testuser', $link->getUrl());
            }
        }

        // Verify X link
        $xLink = $created->getXLink();
        if ($xLink) {
            $link = $xLink->getPrimaryLink();
            if ($link) {
                $this->assertEquals('https://x.com/testuser', $link->getUrl());
            }
        }
    }
}
