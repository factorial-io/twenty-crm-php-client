<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanySearchFilter;
use Factorial\TwentyCrm\DTO\DomainName;
use Factorial\TwentyCrm\DTO\DomainNameCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class CompanyServiceTest extends IntegrationTestCase
{
    public function testGetCompanyById(): void
    {
        $this->requireClient();

        // Note: This test assumes at least one company exists in the backend
        // In a real scenario, you might want to create a company first if the API supports it

        $filter = new CompanySearchFilter();
        $options = new SearchOptions(limit: 1);
        $companies = $this->client->companies()->find($filter, $options);

        if ($companies->isEmpty()) {
            $this->markTestSkipped('No companies available in the backend for testing');
        }

        $firstCompany = $companies->getCompanies()[0];
        $companyId = $firstCompany->getId();

        // Get by ID
        $retrieved = $this->client->companies()->getById($companyId);

        $this->assertNotNull($retrieved);
        $this->assertEquals($companyId, $retrieved->getId());
    }

    public function testGetNonExistentCompanyReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->client->companies()->getById('non-existent-company-id-12345');

        $this->assertNull($result);
    }

    public function testFindCompanies(): void
    {
        $this->requireClient();

        $filter = new CompanySearchFilter();
        $options = new SearchOptions(limit: 10);

        $results = $this->client->companies()->find($filter, $options);

        $this->assertNotNull($results);
        $this->assertLessThanOrEqual(10, $results->count());
    }

    public function testFindCompaniesByName(): void
    {
        $this->requireClient();

        // Get first company to test filtering
        $filter = new CompanySearchFilter();
        $options = new SearchOptions(limit: 1);
        $companies = $this->client->companies()->find($filter, $options);

        if ($companies->isEmpty()) {
            $this->markTestSkipped('No companies available in the backend for testing');
        }

        $company = $companies->getCompanies()[0];
        $companyName = $company->getName();

        // Search by name
        $searchFilter = new CompanySearchFilter(name: $companyName);
        $results = $this->client->companies()->find($searchFilter, new SearchOptions());

        $this->assertGreaterThan(0, $results->count());
    }

    public function testFindCompaniesWithLimit(): void
    {
        $this->requireClient();

        $filter = new CompanySearchFilter();
        $options = new SearchOptions(limit: 5);

        $results = $this->client->companies()->find($filter, $options);

        $this->assertLessThanOrEqual(5, $results->count());
    }

    public function testFindCompaniesWithOrdering(): void
    {
        $this->requireClient();

        $filter = new CompanySearchFilter();
        $optionsAsc = new SearchOptions(limit: 10, orderBy: 'name');

        $resultsAsc = $this->client->companies()->find($filter, $optionsAsc);

        $this->assertNotNull($resultsAsc);

        // Verify we got results
        if ($resultsAsc->count() > 1) {
            $companies = $resultsAsc->getCompanies();
            // Verify ordering (first name should be <= second name)
            $this->assertLessThanOrEqual(
                $companies[1]->getName(),
                $companies[0]->getName()
            );
        }
    }

    public function testCreateCompany(): void
    {
        $this->requireClient();

        // Create a unique company with a real but uncommon domain
        // Note: Using less-common valid domains. If test fails with "Duplicate Domain Name",
        // the domain is already in use in the Twenty CRM database.
        $uniqueId = uniqid('test_');
        $testDomain = 'https://iana.org'; // Internet Assigned Numbers Authority - less likely to be used

        $domainCollection = new DomainNameCollection(
            new DomainName($testDomain)
        );

        $company = new Company(
            name: "Test Company {$uniqueId}",
            domainName: $domainCollection,
            addressCity: 'Test City',
        );

        try {
            $created = $this->client->companies()->create($company);

            $this->assertNotNull($created->getId());
            $this->assertEquals("Test Company {$uniqueId}", $created->getName());
            $this->assertNotNull($created->getDomainName());
            $this->assertEquals($testDomain, $created->getDomainName()->getPrimaryUrl());
            $this->assertEquals('Test City', $created->getAddressCity());

            // Cleanup
            $this->client->companies()->delete($created->getId());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped('Domain already exists in database. Twenty CRM requires unique domains.');
            }
            throw $e;
        }
    }

    public function testCreateCompanyWithMultipleDomains(): void
    {
        $this->requireClient();

        // Create a unique company with multiple real but uncommon domains
        $uniqueId = uniqid('test_');
        $domainCollection = new DomainNameCollection(
            primaryDomainName: new DomainName('https://ietf.org'), // Internet Engineering Task Force
            additionalDomainNames: [
              new DomainName('https://rfc-editor.org'),
              new DomainName('https://ieee.org'),
            ]
        );

        $company = new Company(
            name: "Test Multi-Domain Company {$uniqueId}",
            domainName: $domainCollection,
        );

        try {
            $created = $this->client->companies()->create($company);

            $this->assertNotNull($created->getId());
            $this->assertEquals("Test Multi-Domain Company {$uniqueId}", $created->getName());

            $domains = $created->getDomainName();
            $this->assertNotNull($domains);
            $this->assertEquals('https://ietf.org', $domains->getPrimaryUrl());
            $this->assertCount(2, $domains->getAdditionalDomainNames());

            $additionalDomains = $domains->getAdditionalDomainNames();
            $this->assertEquals('https://rfc-editor.org', $additionalDomains[0]->getUrl());
            $this->assertEquals('https://ieee.org', $additionalDomains[1]->getUrl());

            // Cleanup
            $this->client->companies()->delete($created->getId());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped(
                    'One or more domains already exist in database. Twenty CRM requires unique domains.'
                );
            }
            throw $e;
        }
    }

    public function testUpdateCompany(): void
    {
        $this->requireClient();

        // Create a company first with a real domain
        $uniqueId = uniqid('test_');
        $domainCollection = new DomainNameCollection(
            new DomainName('https://w3.org') // World Wide Web Consortium
        );

        $company = new Company(
            name: "Test Company {$uniqueId}",
            domainName: $domainCollection,
        );

        try {
            $created = $this->client->companies()->create($company);
            $this->assertNotNull($created->getId());

            // Update the company
            $created->setName("Updated Company {$uniqueId}");
            $created->setAddressCity('Updated City');

            $updated = $this->client->companies()->update($created);

            $this->assertEquals("Updated Company {$uniqueId}", $updated->getName());
            $this->assertEquals('Updated City', $updated->getAddressCity());

            // Cleanup
            $this->client->companies()->delete($created->getId());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped('Domain already exists in database. Twenty CRM requires unique domains.');
            }
            throw $e;
        }
    }

    public function testUpdateCompanyDomain(): void
    {
        $this->requireClient();

        // Create a company first with a real domain
        $uniqueId = uniqid('test_');
        $domainCollection = new DomainNameCollection(
            new DomainName('https://unicode.org')
        );

        $company = new Company(
            name: "Test Company {$uniqueId}",
            domainName: $domainCollection,
        );

        try {
            $created = $this->client->companies()->create($company);
            $this->assertNotNull($created->getId());

            // Update domain with a different real domain
            $newDomainCollection = new DomainNameCollection(
                primaryDomainName: new DomainName('https://kernel.org'),
                additionalDomainNames: [
                  new DomainName('https://apache.org'),
                ]
            );
            $created->setDomainName($newDomainCollection);

            $updated = $this->client->companies()->update($created);

            $domains = $updated->getDomainName();
            $this->assertNotNull($domains);
            $this->assertEquals('https://kernel.org', $domains->getPrimaryUrl());
            $this->assertCount(1, $domains->getAdditionalDomainNames());

            // Cleanup
            $this->client->companies()->delete($created->getId());
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped(
                    'One or more domains already exist in database. Twenty CRM requires unique domains.'
                );
            }
            throw $e;
        }
    }

    public function testDeleteCompany(): void
    {
        $this->requireClient();

        // Create a company first with a real domain
        $uniqueId = uniqid('test_');
        $domainCollection = new DomainNameCollection(
            new DomainName('https://mozilla.org')
        );

        $company = new Company(
            name: "Test Company {$uniqueId}",
            domainName: $domainCollection,
        );

        try {
            $created = $this->client->companies()->create($company);
            $this->assertNotNull($created->getId());

            // Delete the company
            $result = $this->client->companies()->delete($created->getId());

            $this->assertTrue($result);

            // Verify deletion
            $retrieved = $this->client->companies()->getById($created->getId());
            $this->assertNull($retrieved);
        } catch (\Factorial\TwentyCrm\Exception\ApiException $e) {
            if (str_contains($e->getResponseBody() ?? '', 'Duplicate Domain Name')) {
                $this->markTestSkipped('Domain already exists in database. Twenty CRM requires unique domains.');
            }
            throw $e;
        }
    }

    public function testDeleteNonExistentCompanyReturnsFalse(): void
    {
        $this->requireClient();

        $result = $this->client->companies()->delete('non-existent-company-id-12345');

        $this->assertFalse($result);
    }
}
