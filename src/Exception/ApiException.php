<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Exception;

/**
 * Exception thrown when API requests fail.
 */
class ApiException extends TwentyCrmException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly ?string $response = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response body.
     */
    public function getResponseBody(): ?string
    {
        return $this->response;
    }
}
