<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\CompanyService;
use Factorial\TwentyCrm\Services\CompanyServiceInterface;
use Factorial\TwentyCrm\Services\ContactService;
use Factorial\TwentyCrm\Services\ContactServiceInterface;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Services\MetadataService;

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

    /**
     * The metadata service instance.
     *
     * @var \Factorial\TwentyCrm\Services\MetadataService|null
     */
    private ?MetadataService $metadataService = null;

    /**
     * The entity registry instance.
     *
     * @var \Factorial\TwentyCrm\Registry\EntityRegistry|null
     */
    private ?EntityRegistry $registry = null;

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

    /**
     * Get the metadata service.
     *
     * @return \Factorial\TwentyCrm\Services\MetadataService
     */
    public function metadata(): MetadataService
    {
        if ($this->metadataService === null) {
            $this->metadataService = new MetadataService($this->httpClient);
        }

        return $this->metadataService;
    }

    /**
     * Get a generic entity service for the specified entity.
     *
     * @param string $name The entity name (e.g., 'person', 'company', 'campaign')
     * @return \Factorial\TwentyCrm\Services\GenericEntityService
     * @throws \InvalidArgumentException If the entity doesn't exist
     */
    public function entity(string $name): GenericEntityService
    {
        $definition = $this->registry()->getDefinition($name);
        if (!$definition) {
            throw new \InvalidArgumentException("Unknown entity: {$name}");
        }

        return new GenericEntityService($this->httpClient, $definition);
    }

    /**
     * Get the entity registry.
     *
     * @return \Factorial\TwentyCrm\Registry\EntityRegistry
     */
    public function registry(): EntityRegistry
    {
        if ($this->registry === null) {
            $this->registry = new EntityRegistry($this->httpClient, $this->metadata());
        }

        return $this->registry;
    }

    /**
     * Get the HTTP client instance.
     *
     * @return \Factorial\TwentyCrm\Http\HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
}
