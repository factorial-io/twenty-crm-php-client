<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Http;

use Factorial\TwentyCrm\Auth\AuthenticationInterface;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Exception\AuthenticationException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Guzzle HTTP client implementation.
 */
final class GuzzleHttpClient implements HttpClientInterface {

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly RequestFactoryInterface $requestFactory,
    private readonly StreamFactoryInterface $streamFactory,
    private readonly AuthenticationInterface $authentication,
    private readonly string $baseUri,
    private readonly LoggerInterface $logger = new NullLogger(),
  ) {}

  /**
   * {@inheritdoc}
   */
  public function request(string $method, string $uri, array $options = []): array {
    $url = rtrim($this->baseUri, '/') . '/' . ltrim($uri, '/');

    // Create request.
    $request = $this->requestFactory->createRequest($method, $url);

    // Add authentication.
    $request = $this->authentication->authenticate($request);

    // Add default headers.
    $request = $request
      ->withHeader('Content-Type', 'application/json')
      ->withHeader('Accept', 'application/json');

    // Add query parameters.
    if (!empty($options['query'])) {
      $query = http_build_query($options['query']);
      $uri = $request->getUri()->withQuery($query);
      $request = $request->withUri($uri);
    }

    // Add request body.
    if (!empty($options['json'])) {
      $body = $this->streamFactory->createStream(json_encode($options['json']));
      $request = $request->withBody($body);
    }

    try {
      $this->logger->debug('Twenty CRM API request', [
        'method' => $method,
        'uri' => $uri,
        'has_body' => !empty($options['json']),
      ]);

      $response = $this->httpClient->sendRequest($request);
      $statusCode = $response->getStatusCode();
      $body = (string) $response->getBody();

      if ($statusCode >= 200 && $statusCode < 300) {
        return json_decode($body, TRUE, 512, JSON_THROW_ON_ERROR);
      }

      // Handle error responses.
      if ($statusCode === 401) {
        throw new AuthenticationException('Authentication failed: ' . $body, $statusCode);
      }

      throw new ApiException('API request failed with status ' . $statusCode, $statusCode, $body);

    }
    catch (ClientException $e) {
      $statusCode = $e->getResponse()->getStatusCode();
      $body = (string) $e->getResponse()->getBody();

      if ($statusCode === 401) {
        throw new AuthenticationException('Authentication failed: ' . $body, $statusCode, $e);
      }

      throw new ApiException('Client error: ' . $e->getMessage(), $statusCode, $body, $e);

    }
    catch (ServerException $e) {
      $statusCode = $e->getResponse()->getStatusCode();
      $body = (string) $e->getResponse()->getBody();

      throw new ApiException('Server error: ' . $e->getMessage(), $statusCode, $body, $e);

    }
    catch (GuzzleException $e) {
      $this->logger->error('HTTP client error: ' . $e->getMessage());
      throw new ApiException('HTTP client error: ' . $e->getMessage(), 0, NULL, $e);

    }
    catch (\JsonException $e) {
      throw new ApiException('Invalid JSON response: ' . $e->getMessage(), 0, NULL, $e);
    }
  }

}
