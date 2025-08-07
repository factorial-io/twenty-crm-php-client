<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Http\HttpClientInterface;

/**
 * Contact service implementation.
 */
final class ContactService implements ContactServiceInterface {

  public function __construct(
    private readonly HttpClientInterface $httpClient,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function find(FilterInterface $filter, SearchOptions $options): array {
    $queryParams = $options->toQueryParams();

    // Add filter if any filters are set.
    if ($filter->hasFilters()) {
      $queryParams['filter'] = $filter->buildFilterString();
    }

    $requestOptions = ['query' => $queryParams];

    return $this->httpClient->request('GET', '/people', $requestOptions);
  }

  /**
   * {@inheritdoc}
   */
  public function getById(string $id): ?array {
    try {
      return $this->httpClient->request('GET', '/people/' . $id);
    }
    catch (\Exception $e) {
      // If not found or other error, return null.
      return NULL;
    }
  }

}
