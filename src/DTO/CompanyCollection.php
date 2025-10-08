<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Collection of companies with pagination info.
 */
class CompanyCollection extends AbstractCollection
{
    /**
     * Create CompanyCollection from API response.
     *
     * @param array $response
     *   The API response data.
     * @param \Factorial\TwentyCrm\Http\HttpClientInterface|null $httpClient
     *   HTTP client for lazy loading.
     * @param mixed $originalFilter
     *   Original filter for pagination.
     * @param mixed $originalSearchOptions
     *   Original search options for pagination.
     *
     * @return self
     *   The CompanyCollection instance.
     */
    public static function fromApiResponse(
        array $response,
        $httpClient = null,
        $originalFilter = null,
        $originalSearchOptions = null
    ): self {
        $companies = [];

        // Handle Twenty CRM REST API response structure based on OpenAPI spec
        // Response format: { data: { companies: [...] }, pageInfo: {...}, totalCount: ... }
        if (isset($response['data']['companies'])) {
            foreach ($response['data']['companies'] as $companyData) {
                $companies[] = Company::fromArray($companyData);
            }
        } elseif (isset($response['data']) && is_array($response['data'])) {
            // Fallback: if data is directly an array of companies
            foreach ($response['data'] as $companyData) {
                $companies[] = Company::fromArray($companyData);
            }
        }

        $paginationInfo = self::extractPaginationInfo($response);

        return new self(
            items: $companies,
            total: $paginationInfo['total'] ?: count($companies),
            page: $paginationInfo['page'],
            pageSize: count($companies),
            hasMore: $paginationInfo['hasMore'],
            startCursor: $paginationInfo['startCursor'],
            endCursor: $paginationInfo['endCursor'],
            httpClient: $httpClient,
            originalFilter: $originalFilter,
            originalSearchOptions: $originalSearchOptions,
        );
    }

    /**
     * Get companies.
     *
     * @return Company[]
     */
    public function getCompanies(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    protected function setItems(array $items): self
    {
        $this->items = [];
        foreach ($items as $company) {
            if ($company instanceof Company) {
                $this->items[] = $company;
            }
        }

        return $this;
    }

    /**
     * Add a company to the collection.
     *
     * @param Company $company
     *
     * @return self
     */
    public function addCompany(Company $company): self
    {
        $this->items[] = $company;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_map(fn (Company $company) => $company->toArray(), $this->items);
    }

    /**
     * {@inheritdoc}
     */
    protected function makeNextPageRequest($filter, $options): array
    {
        $queryParams = $options->toQueryParams();

        // Add filter if any filters are set.
        if ($filter->hasFilters()) {
            $queryParams['filter'] = $filter->buildFilterString();
        }

        $requestOptions = ['query' => $queryParams];

        return $this->httpClient->request('GET', '/companies', $requestOptions);
    }
}
