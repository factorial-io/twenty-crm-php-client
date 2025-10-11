<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\DomainName;
use Factorial\TwentyCrm\DTO\DomainNameCollection;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for person (contact) to company relations.
 */
class PersonCompanyRelationTest extends IntegrationTestCase
{
    public function testCreatePersonWithCompanyRelation(): void
    {
        $this->requireClient();

        // Step 1: Create a company first
        $uniqueId = uniqid('test_');
        $testDomain = 'https://fsf.org'; // Free Software Foundation

        $domainCollection = new DomainNameCollection(
            new DomainName($testDomain)
        );

        $company = new Company(
            name: "Acme Corporation {$uniqueId}",
            domainName: $domainCollection,
            addressCity: 'New York',
            addressCountry: 'USA'
        );

        try {
            $createdCompany = $this->client->companies()->create($company);
            // $this->trackResource('company', $createdCompany->getId()); // Disabled for manual inspection

            $this->assertNotNull($createdCompany->getId());
            $this->assertEquals("Acme Corporation {$uniqueId}", $createdCompany->getName());
            $this->assertEquals('New York', $createdCompany->getAddressCity());

            // Step 2: Create a person (contact) associated with the company
            $contact = new Contact(
                email: $this->generateTestEmail(),
                firstName: $this->generateTestName('John'),
                lastName: 'Doe',
                jobTitle: 'Software Engineer',
                companyId: $createdCompany->getId()
            );

            $createdContact = $this->client->contacts()->create($contact);
            // $this->trackResource('contact', $createdContact->getId()); // Disabled for manual inspection

            $this->assertNotNull($createdContact->getId());
            $this->assertEquals($contact->getEmail(), $createdContact->getEmail());
            $this->assertEquals($contact->getFirstName(), $createdContact->getFirstName());
            $this->assertEquals($contact->getLastName(), $createdContact->getLastName());
            $this->assertEquals($contact->getJobTitle(), $createdContact->getJobTitle());
            $this->assertEquals($createdCompany->getId(), $createdContact->getCompanyId());

            // Step 3: Retrieve the person and verify the company relation exists
            $retrievedContact = $this->client->contacts()->getById($createdContact->getId());

            $this->assertNotNull($retrievedContact);
            $this->assertEquals($createdContact->getId(), $retrievedContact->getId());
            $this->assertEquals($createdCompany->getId(), $retrievedContact->getCompanyId());
            $this->assertEquals('Software Engineer', $retrievedContact->getJobTitle());

            // Step 4: Retrieve the company and verify it was created correctly
            $retrievedCompany = $this->client->companies()->getById($createdCompany->getId());

            $this->assertNotNull($retrievedCompany);
            $this->assertEquals($createdCompany->getId(), $retrievedCompany->getId());
            $this->assertEquals("Acme Corporation {$uniqueId}", $retrievedCompany->getName());
            $this->assertEquals('New York', $retrievedCompany->getAddressCity());
            $this->assertEquals('USA', $retrievedCompany->getAddressCountry());

            // Verify domain
            $this->assertNotNull($retrievedCompany->getDomainName());
            $this->assertEquals($testDomain, $retrievedCompany->getDomainName()->getPrimaryUrl());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped('Domain already exists in database. Twenty CRM requires unique domains.');
            }
            throw $e;
        }
    }

    public function testUpdatePersonCompanyRelation(): void
    {
        $this->requireClient();

        // Create two companies
        $uniqueId1 = uniqid('test_');
        $uniqueId2 = uniqid('test_');

        $company1 = new Company(
            name: "Company One {$uniqueId1}",
            domainName: new DomainNameCollection(
                new DomainName('https://eff.org') // Electronic Frontier Foundation
            ),
            addressCity: 'Boston'
        );

        $company2 = new Company(
            name: "Company Two {$uniqueId2}",
            domainName: new DomainNameCollection(
                new DomainName('https://python.org') // Python Software Foundation
            ),
            addressCity: 'San Francisco'
        );

        try {
            $createdCompany1 = $this->client->companies()->create($company1);
            // $this->trackResource('company', $createdCompany1->getId()); // Disabled for manual inspection

            $createdCompany2 = $this->client->companies()->create($company2);
            // $this->trackResource('company', $createdCompany2->getId()); // Disabled for manual inspection

            // Create a contact associated with company 1
            $contact = new Contact(
                email: $this->generateTestEmail(),
                firstName: $this->generateTestName('Jane'),
                lastName: 'Smith',
                jobTitle: 'Product Manager',
                companyId: $createdCompany1->getId()
            );

            $createdContact = $this->client->contacts()->create($contact);
            // $this->trackResource('contact', $createdContact->getId()); // Disabled for manual inspection

            // Verify initial company relationship
            $this->assertEquals($createdCompany1->getId(), $createdContact->getCompanyId());

            // Update the contact to associate with company 2
            $createdContact->setCompanyId($createdCompany2->getId());
            $updatedContact = $this->client->contacts()->update($createdContact);

            // Verify the company relationship was updated
            $this->assertEquals($createdCompany2->getId(), $updatedContact->getCompanyId());

            // Retrieve and verify
            $retrievedContact = $this->client->contacts()->getById($updatedContact->getId());
            $this->assertEquals($createdCompany2->getId(), $retrievedContact->getCompanyId());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped('Domain already exists in database. Twenty CRM requires unique domains.');
            }
            throw $e;
        }
    }

    public function testCreatePersonWithoutCompany(): void
    {
        $this->requireClient();

        // Create a contact without a company association
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('Alice'),
            lastName: 'Johnson',
            jobTitle: 'Freelance Consultant'
        );

        $createdContact = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $createdContact->getId());

        $this->assertNotNull($createdContact->getId());
        $this->assertNull($createdContact->getCompanyId());
        $this->assertEquals('Freelance Consultant', $createdContact->getJobTitle());

        // Retrieve and verify
        $retrievedContact = $this->client->contacts()->getById($createdContact->getId());
        $this->assertNotNull($retrievedContact);
        $this->assertNull($retrievedContact->getCompanyId());
    }
}
