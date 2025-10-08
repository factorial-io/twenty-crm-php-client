<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Auth;

use Psr\Http\Message\RequestInterface;

/**
 * Bearer token authentication implementation.
 */
final class BearerTokenAuth implements AuthenticationInterface
{
    public function __construct(
        private readonly string $token,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->token);
    }
}
