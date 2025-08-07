<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Exception;

/**
 * Exception thrown when API requests fail.
 */
class ApiException extends TwentyCrmException {

  public function __construct(
    string $message,
    public readonly int $statusCode = 0,
    public readonly ?string $response = NULL,
    ?\Throwable $previous = NULL,
  ) {
    parent::__construct($message, $statusCode, $previous);
  }

}
