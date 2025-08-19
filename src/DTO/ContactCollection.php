<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Collection of contacts with pagination info.
 */
class ContactCollection {

  /**
   * @var Contact[]
   */
  private array $contacts = [];

  /**
   * ContactCollection constructor.
   *
   * @param Contact[] $contacts
   *   Array of Contact objects.
   * @param int $total
   *   Total number of contacts.
   * @param int $page
   *   Current page number.
   * @param int $pageSize
   *   Number of items per page.
   * @param bool $hasMore
   *   Whether there are more pages.
   */
  public function __construct(
    array $contacts = [],
    private int $total = 0,
    private int $page = 1,
    private int $pageSize = 20,
    private bool $hasMore = false,
  ) {
    $this->setContacts($contacts);
  }

  /**
   * Create ContactCollection from API response.
   *
   * @param array $response
   *   The API response data.
   *
   * @return self
   *   The ContactCollection instance.
   */
  public static function fromApiResponse(array $response): self {
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
    
    $pageInfo = $response['pageInfo'] ?? [];
    
    return new self(
      contacts: $contacts,
      total: $response['totalCount'] ?? count($contacts),
      page: 1, // Twenty CRM uses cursor-based pagination
      pageSize: count($contacts),
      hasMore: $pageInfo['hasNextPage'] ?? false,
    );
  }

  /**
   * Get contacts.
   *
   * @return Contact[]
   */
  public function getContacts(): array {
    return $this->contacts;
  }

  /**
   * Set contacts.
   *
   * @param Contact[] $contacts
   *
   * @return self
   */
  public function setContacts(array $contacts): self {
    $this->contacts = [];
    foreach ($contacts as $contact) {
      if ($contact instanceof Contact) {
        $this->contacts[] = $contact;
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
  public function addContact(Contact $contact): self {
    $this->contacts[] = $contact;
    return $this;
  }

  /**
   * Get total count.
   *
   * @return int
   */
  public function getTotal(): int {
    return $this->total;
  }

  /**
   * Get current page.
   *
   * @return int
   */
  public function getPage(): int {
    return $this->page;
  }

  /**
   * Get page size.
   *
   * @return int
   */
  public function getPageSize(): int {
    return $this->pageSize;
  }

  /**
   * Check if there are more pages.
   *
   * @return bool
   */
  public function hasMore(): bool {
    return $this->hasMore;
  }

  /**
   * Count contacts in current page.
   *
   * @return int
   */
  public function count(): int {
    return count($this->contacts);
  }

  /**
   * Check if collection is empty.
   *
   * @return bool
   */
  public function isEmpty(): bool {
    return empty($this->contacts);
  }

  /**
   * Get contacts as array.
   *
   * @return array
   */
  public function toArray(): array {
    return array_map(fn(Contact $contact) => $contact->toArray(), $this->contacts);
  }

}