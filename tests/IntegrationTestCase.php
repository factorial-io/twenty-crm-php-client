<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests;

use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

/**
 * Base test case for integration tests against real Twenty backend.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected ?TwentyCrmClient $client = null;
    protected array $createdResources = [];

    protected function setUp(): void
    {
        parent::setUp();

        if ($_ENV['TWENTY_TEST_MODE'] !== 'integration') {
            $this->markTestSkipped('Integration tests are disabled. Set TWENTY_TEST_MODE=integration to run.');
        }

        $this->validateEnvironment();
        $this->client = $this->createClient();
    }

    protected function tearDown(): void
    {
        $this->cleanupResources();
        parent::tearDown();
    }

    /**
     * Validate required environment variables.
     */
    private function validateEnvironment(): void
    {
        $required = ['TWENTY_API_BASE_URI', 'TWENTY_API_TOKEN'];

        foreach ($required as $var) {
            if (empty($_ENV[$var])) {
                $this->fail("Required environment variable {$var} is not set. Check your .env file.");
            }
        }
    }

    /**
     * Create a configured Twenty CRM client.
     */
    protected function createClient(): TwentyCrmClient
    {
        $guzzle = new Client([
            'timeout' => 30,
        ]);

        $httpFactory = new HttpFactory();
        $auth = new BearerTokenAuth($_ENV['TWENTY_API_TOKEN']);

        $httpClient = new GuzzleHttpClient(
            $guzzle,
            $httpFactory,
            $httpFactory,
            $auth,
            $_ENV['TWENTY_API_BASE_URI']
        );

        return new TwentyCrmClient($httpClient);
    }

    /**
     * Track a resource for cleanup after test.
     *
     * @param string $type Resource type (e.g., 'contact', 'company')
     * @param string $id Resource ID
     */
    protected function trackResource(string $type, string $id): void
    {
        $this->createdResources[] = ['type' => $type, 'id' => $id];
    }

    /**
     * Clean up all created resources.
     */
    protected function cleanupResources(): void
    {
        foreach (array_reverse($this->createdResources) as $resource) {
            try {
                match ($resource['type']) {
                    'contact' => $this->client->contacts()->delete($resource['id']),
                    'company' => $this->client->companies()->delete($resource['id']),
                    default => null,
                };
            } catch (\Exception $e) {
                // Ignore cleanup errors - resource may already be deleted
            }
        }

        $this->createdResources = [];
    }

    /**
     * Generate a unique test name with prefix.
     */
    protected function generateTestName(string $base): string
    {
        $prefix = $this->getTestPrefix();
        $timestamp = time();
        return "{$prefix}{$base}_{$timestamp}";
    }

    /**
     * Generate a unique test email.
     */
    protected function generateTestEmail(): string
    {
        $id = $this->createTestId();
        return "test_{$id}@example.com";
    }

    /**
     * Skip if client is not available.
     */
    protected function requireClient(): void
    {
        if (!$this->client) {
            $this->fail('Client not initialized. Check your environment configuration.');
        }
    }
}
