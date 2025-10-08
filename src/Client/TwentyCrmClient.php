<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Services\CompanyService;
use Factorial\TwentyCrm\Services\CompanyServiceInterface;
use Factorial\TwentyCrm\Services\ContactService;
use Factorial\TwentyCrm\Services\ContactServiceInterface;

/**
 * Main Twenty CRM client implementation.
 */
final class TwentyCrmClient implements ClientInterface
{
    /**
     * The contact service instance.
     *
     * @var \Factorial\TwentyCrm\Services\ContactServiceInterface|null
     */
    private ?ContactServiceInterface $contactService = null;

    /**
     * The company service instance.
     *
     * @var \Factorial\TwentyCrm\Services\CompanyServiceInterface|null
     */
    private ?CompanyServiceInterface $companyService = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function contacts(): ContactServiceInterface
    {
        if ($this->contactService === null) {
            $this->contactService = new ContactService($this->httpClient);
        }

        return $this->contactService;
    }

    /**
     * {@inheritdoc}
     */
    public function companies(): CompanyServiceInterface
    {
        if ($this->companyService === null) {
            $this->companyService = new CompanyService($this->httpClient);
        }

        return $this->companyService;
    }
}
