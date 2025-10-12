<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\Query\FilterBuilder;
use Factorial\TwentyCrm\DTO\Link;
use Factorial\TwentyCrm\Collection\LinkCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class CompanyServiceTest extends IntegrationTestCase
{
    public function testGetCompanyById(): void
    {
        $this->requireClient();

        // Note: This test assumes at least one company exists in the backend
        $filter = new CustomFilter(null);
        $options = new SearchOptions(limit: 1);
        $companies = $this->getCompanyService()->find($filter, $options);

        if ($companies->isEmpty()) {
            $this->markTestSkipped('No companies available in the backend for testing');
        }

        $firstCompany = $companies->first();
        $companyId = $firstCompany->getId();

        // Get by ID
        $retrieved = $this->getCompanyService()->getById($companyId);

        $this->assertNotNull($retrieved);
        $this->assertEquals($companyId, $retrieved->getId());
    }

    public function testGetNonExistentCompanyReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->getCompanyService()->getById('non-existent-company-id-12345');

        $this->assertNull($result);
    }

    public function testFindCompanies(): void
    {
        $this->requireClient();

        $filter = new CustomFilter(null);
        $options = new SearchOptions(limit: 10);

        $results = $this->getCompanyService()->find($filter, $options);

        $this->assertNotNull($results);
        $this->assertLessThanOrEqual(10, $results->count());
    }

    public function testFindCompaniesByName(): void
    {
        $this->requireClient();

        // Get first company to test filtering
        $filter = new CustomFilter(null);
        $options = new SearchOptions(limit: 1);
        $companies = $this->getCompanyService()->find($filter, $options);

        if ($companies->isEmpty()) {
            $this->markTestSkipped('No companies available in the backend for testing');
        }

        $company = $companies->first();
        $companyName = $company->getName();

        // Search by name
        $searchFilter = FilterBuilder::create()
            ->equals('name', $companyName)
            ->build();
        $results = $this->getCompanyService()->find($searchFilter, new SearchOptions());

        $this->assertGreaterThan(0, $results->count());
    }

    public function testFindCompaniesWithLimit(): void
    {
        $this->requireClient();

        $filter = new CustomFilter(null);
        $options = new SearchOptions(limit: 5);

        $results = $this->getCompanyService()->find($filter, $options);

        $this->assertLessThanOrEqual(5, $results->count());
    }

    public function testFindCompaniesWithOrdering(): void
    {
        $this->requireClient();

        $filter = new CustomFilter(null);
        $optionsAsc = new SearchOptions(limit: 10, orderBy: 'name');

        $resultsAsc = $this->getCompanyService()->find($filter, $optionsAsc);

        $this->assertNotNull($resultsAsc);

        // Verify we got results
        if ($resultsAsc->count() > 1) {
            $companies = $resultsAsc->getEntities();
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

        // Create a unique company with a real but uncommon domain using new array approach
        $uniqueId = uniqid('test_');
        $testDomain = 'https://iana.org';

        $company = $this->getCompanyService()->createInstance([
            'name' => "Test Company {$uniqueId}",
            'domainName' => new LinkCollection(
                primaryLink: new Link($testDomain, 'Test Company')
            ),
            'address' => new Address(addressCity: 'Test City'),
        ]);

        try {
            $created = $this->getCompanyService()->create($company);
            $this->trackResource('company', $created->getId());

            $this->assertNotNull($created->getId());
            $this->assertEquals("Test Company {$uniqueId}", $created->getName());
            $this->assertNotNull($created->getDomainName());
            $this->assertEquals($testDomain, $created->getDomainName()->getPrimaryLink()->getUrl());
            $this->assertNotNull($created->getAddress());
            $this->assertEquals('Test City', $created->getAddress()->getCity());
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

        // Create a unique company with multiple real but uncommon domains using array approach
        $uniqueId = uniqid('test_');

        $company = $this->getCompanyService()->createInstance([
            'name' => "Test Multi-Domain Company {$uniqueId}",
            'domainName' => new LinkCollection(
                primaryLink: new Link('https://ietf.org', 'IETF'),
                secondaryLinks: [
                    new Link('https://rfc-editor.org', 'RFC Editor'),
                    new Link('https://ieee.org', 'IEEE'),
                ]
            ),
        ]);

        try {
            $created = $this->getCompanyService()->create($company);
            $this->trackResource('company', $created->getId());

            $this->assertNotNull($created->getId());
            $this->assertEquals("Test Multi-Domain Company {$uniqueId}", $created->getName());

            $domains = $created->getDomainName();
            $this->assertNotNull($domains);
            $this->assertEquals('https://ietf.org', $domains->getPrimaryLink()->getUrl());
            $this->assertCount(2, $domains->getSecondaryLinks());

            $additionalDomains = $domains->getSecondaryLinks();
            $this->assertEquals('https://rfc-editor.org', $additionalDomains[0]->getUrl());
            $this->assertEquals('https://ieee.org', $additionalDomains[1]->getUrl());
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
        $domainCollection = new LinkCollection(
            primaryLink: new Link('https://w3.org', 'W3C')
        );

        $company = $this->getCompanyService()->createInstance();
        $company->setName("Test Company {$uniqueId}");
        $company->setDomainName($domainCollection);

        try {
            $created = $this->getCompanyService()->create($company);
            $this->trackResource('company', $created->getId());
            $this->assertNotNull($created->getId());

            // Update the company
            $created->setName("Updated Company {$uniqueId}");
            $created->setAddress(new Address(addressCity: 'Updated City'));

            $updated = $this->getCompanyService()->update($created);

            $this->assertEquals("Updated Company {$uniqueId}", $updated->getName());
            $this->assertNotNull($updated->getAddress());
            $this->assertEquals('Updated City', $updated->getAddress()->getCity());
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
        $domainCollection = new LinkCollection(
            primaryLink: new Link('https://unicode.org', 'Unicode')
        );

        $company = $this->getCompanyService()->createInstance();
        $company->setName("Test Company {$uniqueId}");
        $company->setDomainName($domainCollection);

        try {
            $created = $this->getCompanyService()->create($company);
            $this->trackResource('company', $created->getId());
            $this->assertNotNull($created->getId());

            // Update domain with a different real domain
            $newDomainCollection = new LinkCollection(
                primaryLink: new Link('https://kernel.org', 'Kernel'),
                secondaryLinks: [
                  new Link('https://apache.org', 'Apache'),
                ]
            );
            $created->setDomainName($newDomainCollection);

            $updated = $this->getCompanyService()->update($created);

            $domains = $updated->getDomainName();
            $this->assertNotNull($domains);
            $this->assertEquals('https://kernel.org', $domains->getPrimaryLink()->getUrl());
            $this->assertCount(1, $domains->getSecondaryLinks());
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
        $domainCollection = new LinkCollection(
            primaryLink: new Link('https://mozilla.org', 'Mozilla')
        );

        $company = $this->getCompanyService()->createInstance();
        $company->setName("Test Company {$uniqueId}");
        $company->setDomainName($domainCollection);

        try {
            $created = $this->getCompanyService()->create($company);
            $companyId = $created->getId();
            $this->assertNotNull($companyId);

            // Delete the company
            $result = $this->getCompanyService()->delete($companyId);

            $this->assertTrue($result);

            // Verify deletion
            $retrieved = $this->getCompanyService()->getById($companyId);
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

        $result = $this->getCompanyService()->delete('non-existent-company-id-12345');

        $this->assertFalse($result);
    }
}
