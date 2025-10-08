<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for company search filters.
 */
final class CompanySearchFilter implements FilterInterface
{
    /**
     * Constructs a CompanySearchFilter object.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $domainName = null,
        public readonly ?string $address = null,
        public readonly ?string $employees = null,
        public readonly ?string $linkedinLink = null,
        public readonly ?string $xLink = null,
        public readonly ?string $annualRecurringRevenue = null,
        public readonly ?string $idealCustomerProfile = null,
    ) {
    }

    /**
     * Build filter string for the Twenty CRM API.
     *
     * @return string|null
     *   The filter string or NULL if no filters are set.
     */
    public function buildFilterString(): ?string
    {
        $filterParts = [];

        if ($this->name !== null) {
            $filterParts[] = 'name[ilike]:' . $this->name . '%';
        }

        if ($this->domainName !== null) {
            $filterParts[] = 'domainName[ilike]:' . $this->domainName . '%';
        }

        if ($this->address !== null) {
            $filterParts[] = 'address[ilike]:' . $this->address . '%';
        }

        if ($this->employees !== null) {
            $filterParts[] = 'employees[eq]:' . $this->employees;
        }

        if ($this->linkedinLink !== null) {
            $filterParts[] = 'linkedinLink[ilike]:' . $this->linkedinLink . '%';
        }

        if ($this->xLink !== null) {
            $filterParts[] = 'xLink[ilike]:' . $this->xLink . '%';
        }

        if ($this->annualRecurringRevenue !== null) {
            $filterParts[] = 'annualRecurringRevenue[eq]:' . $this->annualRecurringRevenue;
        }

        if ($this->idealCustomerProfile !== null) {
            $filterParts[] = 'idealCustomerProfile[eq]:' . $this->idealCustomerProfile;
        }

        if (empty($filterParts)) {
            return null;
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
    public function hasFilters(): bool
    {
        return $this->name !== null
                || $this->domainName !== null
                || $this->address !== null
                || $this->employees !== null
                || $this->linkedinLink !== null
                || $this->xLink !== null
                || $this->annualRecurringRevenue !== null
                || $this->idealCustomerProfile !== null;
    }
}
