<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case for all tests.
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Get a unique test prefix.
     */
    protected function getTestPrefix(): string
    {
        return 'Test_';
    }

    /**
     * Create a unique test ID.
     */
    protected function createTestId(): string
    {
        return uniqid('test_', true);
    }
}
