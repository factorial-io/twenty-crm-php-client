<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Http\HttpClientInterface;

/**
 * Company service implementation.
 */
final class CompanyService implements CompanyServiceInterface {

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

    return $this->httpClient->request('GET', '/companies', $requestOptions);
  }

  /**
   * {@inheritdoc}
   */
  public function getById(string $id): ?array {
    try {
      return $this->httpClient->request('GET', '/companies/' . $id);
    }
    catch (\Exception $e) {
      // If not found or other error, return null.
      return NULL;
    }
  }

}
