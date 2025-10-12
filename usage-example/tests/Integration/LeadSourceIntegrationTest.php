<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\Collection\EmailCollection;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for leadSource field functionality.
 */
class LeadSourceIntegrationTest extends IntegrationTestCase
{
    public function testCreatePersonWithLeadSource(): void
    {
        $this->requireClient();

        // Create a person with a lead source (using valid enum value)
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('LeadTest'),
            lastName: 'User'
        ));
        $person->setJobTitle('Marketing Manager');
        $person->setLeadSource('INBOUND');

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        $this->assertNotNull($createdPerson->getId());
        $this->assertEquals($person->getEmails()->getPrimaryEmail(), $createdPerson->getEmails()->getPrimaryEmail());
        $this->assertEquals($person->getName()->getFirstName(), $createdPerson->getName()->getFirstName());
        $this->assertEquals($person->getName()->getLastName(), $createdPerson->getName()->getLastName());
        $this->assertEquals('INBOUND', $createdPerson->getLeadSource());

        // Retrieve and verify the lead source persisted
        $retrievedPerson = $this->getPersonService()->getById($createdPerson->getId());

        $this->assertNotNull($retrievedPerson);
        $this->assertEquals($createdPerson->getId(), $retrievedPerson->getId());
        $this->assertEquals('INBOUND', $retrievedPerson->getLeadSource());
    }

    public function testUpdatePersonLeadSource(): void
    {
        $this->requireClient();

        // Create a person with initial lead source (using valid enum value)
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('UpdateLead'),
            lastName: 'User'
        ));
        $person->setJobTitle('Sales Rep');
        $person->setLeadSource('INBOUND_PARTNER');

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        // Verify initial lead source
        $this->assertEquals('INBOUND_PARTNER', $createdPerson->getLeadSource());

        // Update the lead source (using valid enum value)
        $createdPerson->setLeadSource('SALES_TEAM');
        $updatedPerson = $this->getPersonService()->update($createdPerson);

        // Verify the lead source was updated
        $this->assertEquals('SALES_TEAM', $updatedPerson->getLeadSource());

        // Retrieve and verify
        $retrievedPerson = $this->getPersonService()->getById($updatedPerson->getId());
        $this->assertEquals('SALES_TEAM', $retrievedPerson->getLeadSource());
    }

    public function testCreatePersonWithoutLeadSource(): void
    {
        $this->requireClient();

        // Create a person without a lead source
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('NoLead'),
            lastName: 'User'
        ));
        $person->setJobTitle('Developer');

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        $this->assertNotNull($createdPerson->getId());
        $this->assertNull($createdPerson->getLeadSource());

        // Retrieve and verify
        $retrievedPerson = $this->getPersonService()->getById($createdPerson->getId());
        $this->assertNotNull($retrievedPerson);
        $this->assertNull($retrievedPerson->getLeadSource());
    }

    public function testUpdateBetweenDifferentLeadSources(): void
    {
        $this->requireClient();

        // Create a person with a lead source (using valid enum value)
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('ChangeLead'),
            lastName: 'User'
        ));
        $person->setJobTitle('Product Manager');
        $person->setLeadSource('OUTBOUND_OTHER');

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        // Verify initial lead source
        $this->assertEquals('OUTBOUND_OTHER', $createdPerson->getLeadSource());

        // Change to a different lead source
        $createdPerson->setLeadSource('CALENDAR_EVENT');
        $updatedPerson = $this->getPersonService()->update($createdPerson);

        // Verify the lead source was updated
        $this->assertEquals('CALENDAR_EVENT', $updatedPerson->getLeadSource());

        // Retrieve and verify
        $retrievedPerson = $this->getPersonService()->getById($updatedPerson->getId());
        $this->assertEquals('CALENDAR_EVENT', $retrievedPerson->getLeadSource());
    }
}
