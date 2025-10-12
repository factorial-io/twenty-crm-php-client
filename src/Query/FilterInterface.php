<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Query;

/**
 * Interface for search filter DTOs.
 */
interface FilterInterface
{
    /**
     * Build filter string for the Twenty CRM API.
     *
     * @return string|null
     *   The filter string or NULL if no filters are set.
     */
    public function buildFilterString(): ?string;

    /**
     * Check if any filters are set.
     *
     * @return bool
     *   TRUE if any filters are set, FALSE otherwise.
     */
    public function hasFilters(): bool;
}
