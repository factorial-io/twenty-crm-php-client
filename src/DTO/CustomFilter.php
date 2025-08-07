<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Data Transfer Object for custom handcoded filter strings.
 */
final class CustomFilter implements FilterInterface {

  /**
   * Constructs a CustomFilter object.
   *
   * @param string|null $filterString
   *   The raw filter string to be passed to the API.
   */
  public function __construct(
    public readonly ?string $filterString = NULL,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function buildFilterString(): ?string {
    return $this->filterString;
  }

  /**
   * {@inheritdoc}
   */
  public function hasFilters(): bool {
    return $this->filterString !== NULL && trim($this->filterString) !== '';
  }

  /**
   * Create a custom filter from a raw filter string.
   *
   * @param string $filterString
   *   The raw filter string.
   *
   * @return static
   *   A new CustomFilter instance.
   */
  public static function fromString(string $filterString): self {
    return new self($filterString);
  }

}
