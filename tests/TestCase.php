<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for unit tests.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Create a mock HTTP client for testing.
     *
     * @param array $responseData
     * @param int $statusCode
     * @return \Factorial\TwentyCrm\Http\HttpClientInterface
     */
    protected function createMockHttpClient(array $responseData = [], int $statusCode = 200): object
    {
        $mock = $this->createMock(\Factorial\TwentyCrm\Http\HttpClientInterface::class);
        $mock->method('request')
            ->willReturn($responseData);

        return $mock;
    }

    /**
     * Load fixture data from file.
     *
     * @param string $fixtureName
     * @return array
     */
    protected function loadFixture(string $fixtureName): array
    {
        $fixturePath = __DIR__ . '/Fixtures/' . $fixtureName . '.json';

        if (!file_exists($fixturePath)) {
            throw new \RuntimeException("Fixture not found: {$fixturePath}");
        }

        $content = file_get_contents($fixturePath);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Create a unique test identifier.
     *
     * @return string
     */
    protected function createTestId(): string
    {
        return uniqid('test_', true);
    }

    /**
     * Create a test prefix for data isolation.
     *
     * @return string
     */
    protected function getTestPrefix(): string
    {
        return $_ENV['TWENTY_TEST_PREFIX'] ?? 'TEST_';
    }
}
