<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanyCollection;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;

/**
 * Interface for company service operations.
 */
interface CompanyServiceInterface
{
    /**
     * Find companies based on search criteria.
     *
     * @param \Factorial\TwentyCrm\DTO\FilterInterface $filter
     *   The search filter criteria.
     * @param \Factorial\TwentyCrm\DTO\SearchOptions $options
     *   The search options (limit, offset, etc.).
     *
     * @return \Factorial\TwentyCrm\DTO\CompanyCollection
     *   Collection of companies with pagination info.
     *
     * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
     *   When the API request fails.
     */
    public function find(FilterInterface $filter, SearchOptions $options): CompanyCollection;

    /**
     * Get a company by its UUID.
     *
     * @param string $id
     *   The company UUID.
     *
     * @return \Factorial\TwentyCrm\DTO\Company|null
     *   The company or NULL if not found.
     *
     * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
     *   When the API request fails.
     */
    public function getById(string $id): ?Company;

    /**
     * Create a new company.
     *
     * @param \Factorial\TwentyCrm\DTO\Company $company
     *   The company to create.
     *
     * @return \Factorial\TwentyCrm\DTO\Company
     *   The created company with ID.
     *
     * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
     *   When the API request fails.
     */
    public function create(Company $company): Company;

    /**
     * Update an existing company.
     *
     * @param \Factorial\TwentyCrm\DTO\Company $company
     *   The company to update (must have an ID).
     *
     * @return \Factorial\TwentyCrm\DTO\Company
     *   The updated company.
     *
     * @throws \InvalidArgumentException
     *   When the company doesn't have an ID.
     * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
     *   When the API request fails.
     */
    public function update(Company $company): Company;

    /**
     * Delete a company by its UUID.
     *
     * @param string $id
     *   The company UUID.
     *
     * @return bool
     *   TRUE if deleted, FALSE if not found.
     *
     * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
     *   When the API request fails.
     */
    public function delete(string $id): bool;
}
