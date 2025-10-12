<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\EmailCollection;
use Factorial\TwentyCrm\DTO\Link;
use Factorial\TwentyCrm\DTO\LinkCollection;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for person to company relations.
 *
 * Tests the relationship between generated Person and Company entities.
 */
class PersonCompanyRelationTest extends IntegrationTestCase
{
    public function testCreatePersonWithCompanyRelation(): void
    {
        $this->requireClient();

        // Step 1: Create a company first
        $company = $this->getCompanyService()->createInstance();
        $company->setName($this->generateTestName('AcmeCorp'));
        $company->setDomainName(new LinkCollection(
            primaryLink: new Link('https://acme-' . time() . '.example.com', 'Acme Corp')
        ));
        $company->setAddress(new Address(
            addressCity: 'New York',
            addressCountry: 'USA'
        ));

        $createdCompany = $this->getCompanyService()->create($company);
        $this->trackResource('company', $createdCompany->getId());

        $this->assertNotNull($createdCompany->getId());
        $this->assertEquals($company->getName(), $createdCompany->getName());
        $this->assertNotNull($createdCompany->getAddress());
        $this->assertEquals('New York', $createdCompany->getAddress()->getCity());

        // Step 2: Create a person associated with the company
        $person = $this->getPersonService()->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('John'),
            lastName: 'Doe'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Software Engineer');
        $person->setCompany($createdCompany->getId());

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        $this->assertNotNull($createdPerson->getId());
        $this->assertEquals($person->getName()->getFirstName(), $createdPerson->getName()->getFirstName());
        $this->assertEquals($person->getName()->getLastName(), $createdPerson->getName()->getLastName());
        $this->assertEquals($person->getJobTitle(), $createdPerson->getJobTitle());
        $this->assertEquals($createdCompany->getId(), $createdPerson->getCompany());

        // Step 3: Retrieve the person and verify the company relation exists
        $retrievedPerson = $this->getPersonService()->getById($createdPerson->getId());

        $this->assertNotNull($retrievedPerson);
        $this->assertEquals($createdPerson->getId(), $retrievedPerson->getId());
        $this->assertEquals($createdCompany->getId(), $retrievedPerson->getCompany());
        $this->assertEquals('Software Engineer', $retrievedPerson->getJobTitle());

        // Step 4: Retrieve the company and verify it was created correctly
        $retrievedCompany = $this->getCompanyService()->getById($createdCompany->getId());

        $this->assertNotNull($retrievedCompany);
        $this->assertEquals($createdCompany->getId(), $retrievedCompany->getId());
        $this->assertEquals($company->getName(), $retrievedCompany->getName());
        $this->assertNotNull($retrievedCompany->getAddress());
        $this->assertEquals('New York', $retrievedCompany->getAddress()->getCity());
        $this->assertEquals('USA', $retrievedCompany->getAddress()->getCountry());

        // Verify domain
        $this->assertNotNull($retrievedCompany->getDomainName());
        $this->assertNotNull($retrievedCompany->getDomainName()->getPrimaryLink());
    }

    public function testUpdatePersonCompanyRelation(): void
    {
        $this->requireClient();

        // Create two companies
        $company1 = $this->getCompanyService()->createInstance();
        $company1->setName($this->generateTestName('CompanyOne'));
        $company1->setDomainName(new LinkCollection(
            primaryLink: new Link('https://company1-' . time() . '.example.com', 'Company One')
        ));
        $company1->setAddress(new Address(addressCity: 'Boston'));

        $company2 = $this->getCompanyService()->createInstance();
        $company2->setName($this->generateTestName('CompanyTwo'));
        $company2->setDomainName(new LinkCollection(
            primaryLink: new Link('https://company2-' . time() . '.example.com', 'Company Two')
        ));
        $company2->setAddress(new Address(addressCity: 'San Francisco'));

        $createdCompany1 = $this->getCompanyService()->create($company1);
        $this->trackResource('company', $createdCompany1->getId());

        $createdCompany2 = $this->getCompanyService()->create($company2);
        $this->trackResource('company', $createdCompany2->getId());

        // Create a person associated with company 1
        $person = $this->getPersonService()->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('Jane'),
            lastName: 'Smith'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Product Manager');
        $person->setCompany($createdCompany1->getId());

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        // Verify initial company relationship
        $this->assertEquals($createdCompany1->getId(), $createdPerson->getCompany());

        // Update the person to associate with company 2
        $createdPerson->setCompany($createdCompany2->getId());
        $updatedPerson = $this->getPersonService()->update($createdPerson);

        // Verify the company relationship was updated
        $this->assertEquals($createdCompany2->getId(), $updatedPerson->getCompany());

        // Retrieve and verify
        $retrievedPerson = $this->getPersonService()->getById($updatedPerson->getId());
        $this->assertEquals($createdCompany2->getId(), $retrievedPerson->getCompany());
    }

    public function testCreatePersonWithoutCompany(): void
    {
        $this->requireClient();

        // Create a person without a company association
        $person = $this->getPersonService()->createInstance();
        $person->setName(new Name(
            firstName: $this->generateTestName('Alice'),
            lastName: 'Johnson'
        ));
        $person->setEmails(new EmailCollection(
            primaryEmail: $this->generateTestEmail()
        ));
        $person->setJobTitle('Freelance Consultant');

        $createdPerson = $this->getPersonService()->create($person);
        $this->trackResource('person', $createdPerson->getId());

        $this->assertNotNull($createdPerson->getId());
        $this->assertNull($createdPerson->getCompany());
        $this->assertEquals('Freelance Consultant', $createdPerson->getJobTitle());

        // Retrieve and verify
        $retrievedPerson = $this->getPersonService()->getById($createdPerson->getId());
        $this->assertNotNull($retrievedPerson);
        $this->assertNull($retrievedPerson->getCompany());
    }
}
