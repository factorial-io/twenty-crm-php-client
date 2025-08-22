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
interface CompanyServiceInterface {

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

}
