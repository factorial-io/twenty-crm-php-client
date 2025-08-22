<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

use Factorial\TwentyCrm\Http\HttpClientInterface;

/**
 * Abstract base class for collections with pagination info and lazy loading.
 */
abstract class AbstractCollection implements \Iterator, \Countable {

  /**
   * @var array
   */
  protected array $items = [];

  /**
   * Current iterator position.
   */
  protected int $position = 0;

  /**
   * Pagination cursors.
   */
  protected ?string $startCursor = null;
  protected ?string $endCursor = null;
  protected ?string $nextCursor = null;

  /**
   * HTTP client for lazy loading.
   */
  protected ?HttpClientInterface $httpClient = null;

  /**
   * Original search parameters for pagination.
   */
  protected ?FilterInterface $originalFilter = null;
  protected ?SearchOptions $originalSearchOptions = null;

  /**
   * AbstractCollection constructor.
   *
   * @param array $items
   *   Array of items.
   * @param int $total
   *   Total number of items.
   * @param int $page
   *   Current page number.
   * @param int $pageSize
   *   Number of items per page.
   * @param bool $hasMore
   *   Whether there are more pages.
   * @param string|null $startCursor
   *   Start cursor for pagination.
   * @param string|null $endCursor
   *   End cursor for pagination.
   * @param HttpClientInterface|null $httpClient
   *   HTTP client for lazy loading.
   * @param FilterInterface|null $originalFilter
   *   Original filter for pagination.
   * @param SearchOptions|null $originalSearchOptions
   *   Original search options for pagination.
   */
  public function __construct(
    array $items = [],
    protected int $total = 0,
    protected int $page = 1,
    protected int $pageSize = 20,
    protected bool $hasMore = false,
    ?string $startCursor = null,
    ?string $endCursor = null,
    ?HttpClientInterface $httpClient = null,
    $originalFilter = null,
    $originalSearchOptions = null,
  ) {
    $this->setItems($items);
    $this->startCursor = $startCursor;
    $this->endCursor = $endCursor;
    $this->nextCursor = $endCursor; // For next page requests
    $this->httpClient = $httpClient;
    $this->originalFilter = $originalFilter;
    $this->originalSearchOptions = $originalSearchOptions;
  }

  /**
   * Set items in the collection.
   *
   * @param array $items
   *
   * @return self
   */
  abstract protected function setItems(array $items): self;

  /**
   * Get items from the collection.
   *
   * @return array
   */
  public function getItems(): array {
    return $this->items;
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
   * Count items in current collection (implements Countable).
   * Note: This only counts loaded items, not total items across all pages.
   *
   * @return int
   */
  public function count(): int {
    return count($this->items);
  }

  /**
   * Get the current loaded items count.
   *
   * @return int
   */
  public function getLoadedCount(): int {
    return count($this->items);
  }

  /**
   * Check if collection is empty.
   *
   * @return bool
   */
  public function isEmpty(): bool {
    return empty($this->items);
  }

  /**
   * Get items as array.
   *
   * @return array
   */
  abstract public function toArray(): array;

  /**
   * Extract pagination info from API response.
   *
   * @param array $response
   *   The API response.
   *
   * @return array
   *   Array with keys: total, page, pageSize, hasMore, startCursor, endCursor.
   */
  protected static function extractPaginationInfo(array $response): array {
    $pageInfo = $response['pageInfo'] ?? [];
    
    return [
      'total' => $response['totalCount'] ?? 0,
      'page' => 1, // Twenty CRM uses cursor-based pagination
      'pageSize' => 0, // Will be set based on actual items count
      'hasMore' => $pageInfo['hasNextPage'] ?? false,
      'startCursor' => $pageInfo['startCursor'] ?? null,
      'endCursor' => $pageInfo['endCursor'] ?? null,
    ];
  }

  // Iterator Interface Implementation

  /**
   * {@inheritdoc}
   */
  public function current(): mixed {
    if ($this->valid()) {
      return $this->items[$this->position];
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function key(): int {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function next(): void {
    $this->position++;
    
    // Check if we need to load the next page
    if ($this->position >= count($this->items) && $this->hasMore && $this->canLoadNextPage()) {
      $this->loadNextPage();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rewind(): void {
    $this->position = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function valid(): bool {
    return isset($this->items[$this->position]);
  }

  /**
   * Check if we can load the next page.
   *
   * @return bool
   */
  protected function canLoadNextPage(): bool {
    return $this->httpClient !== null &&
           $this->originalFilter !== null &&
           $this->originalSearchOptions !== null &&
           $this->nextCursor !== null;
  }

  /**
   * Load the next page of results.
   */
  protected function loadNextPage(): void {
    if (!$this->canLoadNextPage()) {
      return;
    }

    // Create new SearchOptions with the next cursor
    $nextSearchOptions = new \Factorial\TwentyCrm\DTO\SearchOptions(
      limit: $this->originalSearchOptions->limit,
      orderBy: $this->originalSearchOptions->orderBy,
      depth: $this->originalSearchOptions->depth,
      startingAfter: $this->nextCursor,
    );

    try {
      // Make the API request for the next page
      $response = $this->makeNextPageRequest($this->originalFilter, $nextSearchOptions);
      $nextPageCollection = static::fromApiResponse($response);
      
      // Append the new items to our current collection
      $newItems = $nextPageCollection->getItems();
      $this->items = array_merge($this->items, $newItems);
      
      // Update pagination info
      $this->hasMore = $nextPageCollection->hasMore();
      $this->nextCursor = $nextPageCollection->endCursor;
      $this->pageSize += count($newItems);
      
    } catch (\Exception $e) {
      // If loading fails, mark as no more pages to prevent infinite loops
      $this->hasMore = false;
    }
  }

  /**
   * Make the HTTP request for the next page.
   *
   * This method should be implemented by child classes to make the appropriate API call.
   *
   * @param mixed $filter
   * @param mixed $options
   * @return array
   */
  abstract protected function makeNextPageRequest($filter, $options): array;

  /**
   * Get the end cursor for this collection.
   *
   * @return string|null
   */
  public function getEndCursor(): ?string {
    return $this->endCursor;
  }

  /**
   * Get the start cursor for this collection.
   *
   * @return string|null
   */
  public function getStartCursor(): ?string {
    return $this->startCursor;
  }

}