<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Sends right away; on HTTP 429 waits and retries up to $maxRetries times.
 *
 * Waits Retry-After seconds when the response has it, else $delay seconds
 * doubled per attempt (5, 10, 20 by default).
 */
final class BackoffRateLimiter implements RateLimiterInterface
{
    public function __construct(
        private readonly int $maxRetries = 3,
        private readonly int $delay = 5,
    ) {
    }

    public function beforeRequest(RequestInterface $request): void
    {
    }

    public function shouldRetry(RequestInterface $request, ResponseInterface $response, int $attempt): bool
    {
        if ($attempt >= $this->maxRetries) {
            return false;
        }
        sleep((int) $response->getHeaderLine('Retry-After') ?: $this->delay * 2 ** $attempt);
        return true;
    }
}
