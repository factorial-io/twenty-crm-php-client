<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for contact search filters.
 */
final class ContactSearchFilter implements FilterInterface
{
    /**
     * Constructs a ContactSearchFilter object.
     */
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $companyId = null,
        public readonly ?string $phone = null,
        public readonly ?string $city = null,
        public readonly ?string $jobTitle = null,
        public readonly ?string $seniority = null,
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

        if ($this->email !== null) {
            $filterParts[] = 'emails.primaryEmail[eq]:' . $this->email;
        }

        if ($this->firstName !== null) {
            $filterParts[] = 'name.firstName[ilike]:' . $this->firstName . '%';
        }

        if ($this->lastName !== null) {
            $filterParts[] = 'name.lastName[ilike]:' . $this->lastName . '%';
        }

        if ($this->companyId !== null) {
            $filterParts[] = 'companyId[eq]:' . $this->companyId;
        }

        if ($this->phone !== null) {
            $filterParts[] = 'phones.primaryPhoneNumber[eq]:' . $this->phone;
        }

        if ($this->city !== null) {
            $filterParts[] = 'city[eq]:' . $this->city;
        }

        if ($this->jobTitle !== null) {
            $filterParts[] = 'jobTitle[ilike]:' . $this->jobTitle . '%';
        }

        if ($this->seniority !== null) {
            $filterParts[] = 'seniority[eq]:' . $this->seniority;
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
        return $this->email !== null
                || $this->firstName !== null
                || $this->lastName !== null
                || $this->companyId !== null
                || $this->phone !== null
                || $this->city !== null
                || $this->jobTitle !== null
                || $this->seniority !== null;
    }
}
