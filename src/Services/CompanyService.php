<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanyCollection;
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
  public function find(FilterInterface $filter, SearchOptions $options): CompanyCollection {
    $queryParams = $options->toQueryParams();

    // Add filter if any filters are set.
    if ($filter->hasFilters()) {
      $queryParams['filter'] = $filter->buildFilterString();
    }

    $requestOptions = ['query' => $queryParams];

    $response = $this->httpClient->request('GET', '/companies', $requestOptions);
    return CompanyCollection::fromApiResponse($response, $this->httpClient, $filter, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getById(string $id): ?Company {
    try {
      $response = $this->httpClient->request('GET', '/companies/' . $id);
      if ($response && isset($response['data']['company'])) {
        return Company::fromArray($response['data']['company']);
      }
      return NULL;
    }
    catch (\Exception $e) {
      // If not found or other error, return null.
      return NULL;
    }
  }

}
