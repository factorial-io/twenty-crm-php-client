<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for search options (used by all entity types).
 */
final class SearchOptions {

  /**
   * Constructs a SearchOptions object.
   */
  public function __construct(
    public readonly int $limit = 20,
    public readonly ?string $orderBy = NULL,
    public readonly ?int $depth = NULL,
    public readonly ?string $startingAfter = NULL,
    public readonly ?string $endingBefore = NULL,
  ) {}

  /**
   * Convert to query parameters array.
   *
   * @return array
   *   The query parameters array.
   */
  public function toQueryParams(): array {
    $params = ['limit' => $this->limit];

    if ($this->orderBy !== NULL) {
      $params['order_by'] = $this->orderBy;
    }

    if ($this->depth !== NULL) {
      $params['depth'] = $this->depth;
    }

    if ($this->startingAfter !== NULL) {
      $params['starting_after'] = $this->startingAfter;
    }

    if ($this->endingBefore !== NULL) {
      $params['ending_before'] = $this->endingBefore;
    }

    return $params;
  }

}
