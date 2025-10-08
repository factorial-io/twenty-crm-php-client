<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Collection of contacts with pagination info.
 */
class ContactCollection extends AbstractCollection
{
    /**
     * Create ContactCollection from API response.
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
     * @return static
     *   The ContactCollection instance.
     */
    public static function fromApiResponse(
        array $response,
        $httpClient = null,
        $originalFilter = null,
        $originalSearchOptions = null
    ): static {
        $contacts = [];

        // Handle Twenty CRM REST API response structure based on OpenAPI spec
        // Response format: { data: { people: [...] }, pageInfo: {...}, totalCount: ... }
        if (isset($response['data']['people'])) {
            foreach ($response['data']['people'] as $personData) {
                $contacts[] = Contact::fromArray($personData);
            }
        } elseif (isset($response['data']) && is_array($response['data'])) {
            // Fallback: if data is directly an array of people
            foreach ($response['data'] as $contactData) {
                $contacts[] = Contact::fromArray($contactData);
            }
        }

        $paginationInfo = self::extractPaginationInfo($response);

        return new static(
            items: $contacts,
            total: $paginationInfo['total'] ?: count($contacts),
            page: $paginationInfo['page'],
            pageSize: count($contacts),
            hasMore: $paginationInfo['hasMore'],
            startCursor: $paginationInfo['startCursor'],
            endCursor: $paginationInfo['endCursor'],
            httpClient: $httpClient,
            originalFilter: $originalFilter,
            originalSearchOptions: $originalSearchOptions,
        );
    }

    /**
     * Get contacts.
     *
     * @return Contact[]
     */
    public function getContacts(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    protected function setItems(array $items): self
    {
        $this->items = [];
        foreach ($items as $contact) {
            if ($contact instanceof Contact) {
                $this->items[] = $contact;
            }
        }

        return $this;
    }

    /**
     * Add a contact to the collection.
     *
     * @param Contact $contact
     *
     * @return self
     */
    public function addContact(Contact $contact): self
    {
        $this->items[] = $contact;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_map(fn (Contact $contact) => $contact->toArray(), $this->items);
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

        return $this->httpClient->request('GET', '/people', $requestOptions);
    }
}
