<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Decides how requests are paced and when a rate-limited one is retried.
 *
 * Twenty allows a fixed number of requests per minute per API key. Pass an
 * implementation to GuzzleHttpClient, e.g. one that shares a budget across
 * processes; the default is BackoffRateLimiter.
 */
interface RateLimiterInterface
{
    /**
     * Called before every attempt; may block until the request may be sent.
     */
    public function beforeRequest(RequestInterface $request): void;

    /**
     * Called for a rate-limited response (HTTP 429).
     *
     * @param int $attempt
     *   Retries so far for this request, starting at 0.
     *
     * @return bool
     *   TRUE to send the request again, after waiting as long as needed.
     */
    public function shouldRetry(RequestInterface $request, ResponseInterface $response, int $attempt): bool;
}
