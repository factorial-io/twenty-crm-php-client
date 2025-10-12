<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Services\MetadataService;

/**
 * Interface for Twenty CRM client.
 */
interface ClientInterface
{
    /**
     * Get a generic entity service for the specified entity.
     *
     * @param string $name The entity name (e.g., 'person', 'company', 'campaign')
     * @return \Factorial\TwentyCrm\Services\GenericEntityService
     * @throws \InvalidArgumentException If the entity doesn't exist
     */
    public function entity(string $name): GenericEntityService;

    /**
     * Get the entity registry.
     *
     * @return \Factorial\TwentyCrm\Registry\EntityRegistry
     */
    public function registry(): EntityRegistry;

    /**
     * Get the metadata service.
     *
     * @return \Factorial\TwentyCrm\Services\MetadataService
     */
    public function metadata(): MetadataService;
}
