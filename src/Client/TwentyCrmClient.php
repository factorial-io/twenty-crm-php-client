<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Services\MetadataService;

/**
 * Main Twenty CRM client implementation.
 */
final class TwentyCrmClient implements ClientInterface
{
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
    public function entity(string $name): GenericEntityService
    {
        $definition = $this->registry()->getDefinition($name);
        if (!$definition) {
            throw new \InvalidArgumentException("Unknown entity: {$name}");
        }

        return new GenericEntityService($this->httpClient, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function registry(): EntityRegistry
    {
        if ($this->registry === null) {
            $this->registry = new EntityRegistry($this->httpClient, $this->metadata());
        }

        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function metadata(): MetadataService
    {
        if ($this->metadataService === null) {
            $this->metadataService = new MetadataService($this->httpClient);
        }

        return $this->metadataService;
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
