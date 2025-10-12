<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Services\MetadataService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        $this->logger->debug('Twenty CRM client initialized');
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

        $this->logger->debug('Creating entity service', ['entity' => $name]);

        return new GenericEntityService($this->httpClient, $definition, $this->logger);
    }

    /**
     * {@inheritdoc}
     */
    public function registry(): EntityRegistry
    {
        if ($this->registry === null) {
            $this->registry = new EntityRegistry($this->httpClient, $this->metadata(), $this->logger);
        }

        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function metadata(): MetadataService
    {
        if ($this->metadataService === null) {
            $this->metadataService = new MetadataService($this->httpClient, $this->logger);
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
