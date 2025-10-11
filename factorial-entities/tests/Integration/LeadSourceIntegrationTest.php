<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for leadSource field functionality.
 */
class LeadSourceIntegrationTest extends IntegrationTestCase
{
    public function testCreateContactWithLeadSource(): void
    {
        $this->requireClient();

        // Create a contact with a lead source (using valid enum value)
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('LeadTest'),
            lastName: 'User',
            jobTitle: 'Marketing Manager',
            leadSource: 'INBOUND'
        );

        $createdContact = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $createdContact->getId());

        $this->assertNotNull($createdContact->getId());
        $this->assertEquals($contact->getEmail(), $createdContact->getEmail());
        $this->assertEquals($contact->getFirstName(), $createdContact->getFirstName());
        $this->assertEquals($contact->getLastName(), $createdContact->getLastName());
        $this->assertEquals('INBOUND', $createdContact->getLeadSource());

        // Retrieve and verify the lead source persisted
        $retrievedContact = $this->client->contacts()->getById($createdContact->getId());

        $this->assertNotNull($retrievedContact);
        $this->assertEquals($createdContact->getId(), $retrievedContact->getId());
        $this->assertEquals('INBOUND', $retrievedContact->getLeadSource());
    }

    public function testUpdateContactLeadSource(): void
    {
        $this->requireClient();

        // Create a contact with initial lead source (using valid enum value)
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('UpdateLead'),
            lastName: 'User',
            jobTitle: 'Sales Rep',
            leadSource: 'INBOUND_PARTNER'
        );

        $createdContact = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $createdContact->getId());

        // Verify initial lead source
        $this->assertEquals('INBOUND_PARTNER', $createdContact->getLeadSource());

        // Update the lead source (using valid enum value)
        $createdContact->setLeadSource('SALES_TEAM');
        $updatedContact = $this->client->contacts()->update($createdContact);

        // Verify the lead source was updated
        $this->assertEquals('SALES_TEAM', $updatedContact->getLeadSource());

        // Retrieve and verify
        $retrievedContact = $this->client->contacts()->getById($updatedContact->getId());
        $this->assertEquals('SALES_TEAM', $retrievedContact->getLeadSource());
    }

    public function testCreateContactWithoutLeadSource(): void
    {
        $this->requireClient();

        // Create a contact without a lead source
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('NoLead'),
            lastName: 'User',
            jobTitle: 'Developer'
        );

        $createdContact = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $createdContact->getId());

        $this->assertNotNull($createdContact->getId());
        $this->assertNull($createdContact->getLeadSource());

        // Retrieve and verify
        $retrievedContact = $this->client->contacts()->getById($createdContact->getId());
        $this->assertNotNull($retrievedContact);
        $this->assertNull($retrievedContact->getLeadSource());
    }

    public function testUpdateBetweenDifferentLeadSources(): void
    {
        $this->requireClient();

        // Create a contact with a lead source (using valid enum value)
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('ChangeLead'),
            lastName: 'User',
            jobTitle: 'Product Manager',
            leadSource: 'OUTBOUND_OTHER'
        );

        $createdContact = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $createdContact->getId());

        // Verify initial lead source
        $this->assertEquals('OUTBOUND_OTHER', $createdContact->getLeadSource());

        // Change to a different lead source
        $createdContact->setLeadSource('CALENDAR_EVENT');
        $updatedContact = $this->client->contacts()->update($createdContact);

        // Verify the lead source was updated
        $this->assertEquals('CALENDAR_EVENT', $updatedContact->getLeadSource());

        // Retrieve and verify
        $retrievedContact = $this->client->contacts()->getById($updatedContact->getId());
        $this->assertEquals('CALENDAR_EVENT', $retrievedContact->getLeadSource());
    }
}
