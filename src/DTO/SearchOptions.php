<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for search options (used by all entity types).
 */
final class SearchOptions
{
    /**
     * Constructs a SearchOptions object.
     *
     * @param int $limit Maximum number of results
     * @param string|null $orderBy Field to order by
     * @param int|null $depth Depth for nested entities
     * @param string|null $startingAfter Cursor for pagination (forward)
     * @param string|null $endingBefore Cursor for pagination (backward)
     * @param string[] $with Relations to eager load (e.g., ['company', 'opportunities'])
     */
    public function __construct(
        public readonly int $limit = 20,
        public readonly ?string $orderBy = null,
        public readonly ?int $depth = null,
        public readonly ?string $startingAfter = null,
        public readonly ?string $endingBefore = null,
        public readonly array $with = [],
    ) {
    }

    /**
     * Convert to query parameters array.
     *
     * @return array
     *   The query parameters array.
     */
    public function toQueryParams(): array
    {
        $params = ['limit' => $this->limit];

        if ($this->orderBy !== null) {
            $params['order_by'] = $this->orderBy;
        }

        if ($this->depth !== null) {
            $params['depth'] = $this->depth;
        }

        if ($this->startingAfter !== null) {
            $params['starting_after'] = $this->startingAfter;
        }

        if ($this->endingBefore !== null) {
            $params['ending_before'] = $this->endingBefore;
        }

        return $params;
    }
}
