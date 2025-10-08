<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanySearchFilter;
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
}
