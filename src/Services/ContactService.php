<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\ContactCollection;
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;

/**
 * Contact service implementation.
 */
final class ContactService implements ContactServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(FilterInterface $filter, SearchOptions $options): ContactCollection
    {
        $queryParams = $options->toQueryParams();

        // Add filter if any filters are set.
        if ($filter->hasFilters()) {
            $queryParams['filter'] = $filter->buildFilterString();
        }

        $requestOptions = ['query' => $queryParams];

        $response = $this->httpClient->request('GET', '/people', $requestOptions);

        return ContactCollection::fromApiResponse($response, $this->httpClient, $filter, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getById(string $id): ?Contact
    {
        try {
            $response = $this->httpClient->request('GET', '/people/' . $id);

            return Contact::fromArray($response['data']['person']);
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Contact $contact): Contact
    {
        $data = $contact->toArray();

        $response = $this->httpClient->request('POST', '/people', [
          'json' => $data,
        ]);

        return Contact::fromArray($response['data']['createPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Contact $contact): Contact
    {
        if (!$contact->getId()) {
            throw new \InvalidArgumentException('Contact must have an ID to be updated');
        }

        $data = $contact->toArray();
        unset($data['id']); // Remove ID from body as it's in the URL

        $response = $this->httpClient->request('PATCH', '/people/' . $contact->getId(), [
          'json' => $data,
        ]);

        return Contact::fromArray($response['data']['updatePerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): bool
    {
        try {
            $this->httpClient->request('DELETE', '/people/' . $id);

            return true;
        } catch (ApiException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function batchUpsert(array $contacts): ContactCollection
    {
        $data = array_map(fn (Contact $contact) => $contact->toArray(), $contacts);

        $response = $this->httpClient->request('POST', '/batch/people', [
          'json' => ['data' => $data],
        ]);

        return ContactCollection::fromApiResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        // Temporarily return hardcoded fields until we figure out the metadata endpoint
        return [
          ['name' => 'name', 'label' => 'Name', 'type' => 'TEXT'],
          ['name' => 'emails', 'label' => 'Email', 'type' => 'EMAIL'],
          ['name' => 'phones', 'label' => 'Phone', 'type' => 'PHONE'],
          ['name' => 'jobTitle', 'label' => 'Job Title', 'type' => 'TEXT'],
          ['name' => 'city', 'label' => 'City', 'type' => 'TEXT'],
          ['name' => 'companyId', 'label' => 'Company', 'type' => 'TEXT'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?Contact
    {
        $filter = new ContactSearchFilter(email: $email);

        $options = new SearchOptions(limit: 1);

        $collection = $this->find($filter, $options);

        if ($collection->isEmpty()) {
            return null;
        }

        $contacts = $collection->getContacts();

        return $contacts[0];
    }
}
