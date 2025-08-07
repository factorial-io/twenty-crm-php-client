<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for company search filters.
 */
final class CompanySearchFilter implements FilterInterface {

  /**
   * Constructs a CompanySearchFilter object.
   */
  public function __construct(
    public readonly ?string $name = NULL,
    public readonly ?string $domainName = NULL,
    public readonly ?string $address = NULL,
    public readonly ?string $employees = NULL,
    public readonly ?string $linkedinLink = NULL,
    public readonly ?string $xLink = NULL,
    public readonly ?string $annualRecurringRevenue = NULL,
    public readonly ?string $idealCustomerProfile = NULL,
  ) {}

  /**
   * Build filter string for the Twenty CRM API.
   *
   * @return string|null
   *   The filter string or NULL if no filters are set.
   */
  public function buildFilterString(): ?string {
    $filterParts = [];

    if ($this->name !== NULL) {
      $filterParts[] = 'name[ilike]:' . $this->name . '%';
    }

    if ($this->domainName !== NULL) {
      $filterParts[] = 'domainName[ilike]:' . $this->domainName . '%';
    }

    if ($this->address !== NULL) {
      $filterParts[] = 'address[ilike]:' . $this->address . '%';
    }

    if ($this->employees !== NULL) {
      $filterParts[] = 'employees[eq]:' . $this->employees;
    }

    if ($this->linkedinLink !== NULL) {
      $filterParts[] = 'linkedinLink[ilike]:' . $this->linkedinLink . '%';
    }

    if ($this->xLink !== NULL) {
      $filterParts[] = 'xLink[ilike]:' . $this->xLink . '%';
    }

    if ($this->annualRecurringRevenue !== NULL) {
      $filterParts[] = 'annualRecurringRevenue[eq]:' . $this->annualRecurringRevenue;
    }

    if ($this->idealCustomerProfile !== NULL) {
      $filterParts[] = 'idealCustomerProfile[eq]:' . $this->idealCustomerProfile;
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
    return $this->name !== NULL
            || $this->domainName !== NULL
            || $this->address !== NULL
            || $this->employees !== NULL
            || $this->linkedinLink !== NULL
            || $this->xLink !== NULL
            || $this->annualRecurringRevenue !== NULL
            || $this->idealCustomerProfile !== NULL;
  }

}
