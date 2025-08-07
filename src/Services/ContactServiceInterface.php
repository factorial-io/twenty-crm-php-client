<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;

/**
 * Interface for contact service operations.
 */
interface ContactServiceInterface {

  /**
   * Find contacts based on search criteria.
   *
   * @param \Factorial\TwentyCrm\DTO\FilterInterface $filter
   *   The search filter criteria.
   * @param \Factorial\TwentyCrm\DTO\SearchOptions $options
   *   The search options.
   *
   * @return array
   *   The search results with people data and pagination info.
   */
  public function find(FilterInterface $filter, SearchOptions $options): array;

  /**
   * Get a contact by ID.
   *
   * @param string $id
   *   The contact ID.
   *
   * @return array|null
   *   The contact data or NULL if not found.
   */
  public function getById(string $id): ?array;

}
