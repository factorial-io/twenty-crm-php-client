<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Http;

/**
 * Interface for HTTP client abstraction.
 */
interface HttpClientInterface {

  /**
   * Make an HTTP request.
   *
   * @param string $method
   *   The HTTP method.
   * @param string $uri
   *   The request URI.
   * @param array $options
   *   Request options.
   *
   * @return array
   *   The decoded response data.
   *
   * @throws \Factorial\TwentyCrm\Exception\ApiException
   *   When the request fails.
   */
  public function request(string $method, string $uri, array $options = []): array;

}
