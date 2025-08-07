<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Auth;

use Psr\Http\Message\RequestInterface;

/**
 * Interface for authentication methods.
 */
interface AuthenticationInterface {

  /**
   * Apply authentication to the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The HTTP request to authenticate.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The authenticated request.
   */
  public function authenticate(RequestInterface $request): RequestInterface;

}
