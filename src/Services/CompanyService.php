<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanyCollection;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;

/**
 * Company service implementation.
 */
final class CompanyService implements CompanyServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(FilterInterface $filter, SearchOptions $options): CompanyCollection
    {
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
    public function getById(string $id): ?Company
    {
        try {
            $response = $this->httpClient->request('GET', '/companies/' . $id);
            if ($response && isset($response['data']['company'])) {
                return Company::fromArray($response['data']['company']);
            }

            return null;
        } catch (\Exception $e) {
            // If not found or other error, return null.
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Company $company): Company
    {
        $data = $company->toArray();

        $response = $this->httpClient->request('POST', '/companies', [
          'json' => $data,
        ]);

        return Company::fromArray($response['data']['createCompany']);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Company $company): Company
    {
        if (!$company->getId()) {
            throw new \InvalidArgumentException('Company must have an ID to be updated');
        }

        $data = $company->toArray();
        unset($data['id']); // Remove ID from body as it's in the URL

        $response = $this->httpClient->request('PATCH', '/companies/' . $company->getId(), [
          'json' => $data,
        ]);

        return Company::fromArray($response['data']['updateCompany']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): bool
    {
        try {
            $this->httpClient->request('DELETE', '/companies/' . $id);

            return true;
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return false;
            }
            throw $e;
        }
    }
}
