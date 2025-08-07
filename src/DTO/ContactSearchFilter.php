<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for contact search filters.
 */
final class ContactSearchFilter implements FilterInterface {

  /**
   * Constructs a ContactSearchFilter object.
   */
  public function __construct(
    public readonly ?string $email = NULL,
    public readonly ?string $firstName = NULL,
    public readonly ?string $lastName = NULL,
    public readonly ?string $companyId = NULL,
    public readonly ?string $phone = NULL,
    public readonly ?string $city = NULL,
    public readonly ?string $jobTitle = NULL,
    public readonly ?string $seniority = NULL,
  ) {}

  /**
   * Build filter string for the Twenty CRM API.
   *
   * @return string|null
   *   The filter string or NULL if no filters are set.
   */
  public function buildFilterString(): ?string {
    $filterParts = [];

    if ($this->email !== NULL) {
      $filterParts[] = 'emails.primaryEmail[eq]:' . $this->email;
    }

    if ($this->firstName !== NULL) {
      $filterParts[] = 'name.firstName[ilike]:' . $this->firstName . '%';
    }

    if ($this->lastName !== NULL) {
      $filterParts[] = 'name.lastName[ilike]:' . $this->lastName . '%';
    }

    if ($this->companyId !== NULL) {
      $filterParts[] = 'companyId[eq]:' . $this->companyId;
    }

    if ($this->phone !== NULL) {
      $filterParts[] = 'phones.primaryPhoneNumber[eq]:' . $this->phone;
    }

    if ($this->city !== NULL) {
      $filterParts[] = 'city[eq]:' . $this->city;
    }

    if ($this->jobTitle !== NULL) {
      $filterParts[] = 'jobTitle[ilike]:' . $this->jobTitle . '%';
    }

    if ($this->seniority !== NULL) {
      $filterParts[] = 'seniority[eq]:' . $this->seniority;
    }

    if (empty($filterParts)) {
      return NULL;
    }

    if (count($filterParts) === 1) {
      return $filterParts[0];
    }

    return 'and(' . implode(',', $filterParts) . ')';
  }

  /**
   * Check if any filters are set.
   *
   * @return bool
   *   TRUE if any filters are set, FALSE otherwise.
   */
  public function hasFilters(): bool {
    return $this->email !== NULL
            || $this->firstName !== NULL
            || $this->lastName !== NULL
            || $this->companyId !== NULL
            || $this->phone !== NULL
            || $this->city !== NULL
            || $this->jobTitle !== NULL
            || $this->seniority !== NULL;
  }

}
