<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

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
   * @return array
   *   Array containing company data from the API response.
   *
   * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
   *   When the API request fails.
   */
  public function find(FilterInterface $filter, SearchOptions $options): array;

  /**
   * Get a company by its UUID.
   *
   * @param string $id
   *   The company UUID.
   *
   * @return array|null
   *   The company data or NULL if not found.
   *
   * @throws \Factorial\TwentyCrm\Exception\TwentyCrmException
   *   When the API request fails.
   */
  public function getById(string $id): ?array;

}
