# Testing Guide

This document explains how to run tests for the Twenty CRM PHP Client library.

## Table of Contents

- [Overview](#overview)
- [Test Types](#test-types)
- [Setup](#setup)
- [Running Tests](#running-tests)
- [Integration Tests](#integration-tests)
- [Writing Tests](#writing-tests)
- [Troubleshooting](#troubleshooting)

## Overview

The test suite includes two types of tests:

1. **Unit Tests** - Fast tests using mocked API responses (no credentials required)
2. **Integration Tests** - Tests against a real Twenty CRM backend (requires credentials)

## Test Types

### Unit Tests

Unit tests run without making actual API calls. They use mocked HTTP responses to test the library's logic in isolation.

- **Location**: `tests/Unit/`
- **Requirements**: None - no credentials or API access needed
- **Speed**: Very fast
- **Default**: Run by default with `vendor/bin/phpunit`

### Integration Tests

Integration tests make real API calls to a Twenty CRM backend. These tests verify that the library works correctly with an actual API.

- **Location**: `tests/Integration/`
- **Requirements**: Valid Twenty CRM API credentials
- **Speed**: Slower (depends on API response time)
- **Default**: Skipped unless explicitly enabled

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment (Optional - Only for Integration Tests)

If you want to run integration tests against a real Twenty CRM backend:

1. Copy the example environment file:

```bash
cp .env.example .env
```

2. Edit `.env` and add your Twenty CRM credentials:

```bash
# Your Twenty CRM instance URL
TWENTY_API_BASE_URI=https://your-instance.twenty.com/rest/

# Your API token (obtain from Twenty CRM settings)
TWENTY_API_TOKEN=your_actual_api_token_here

# Set to 'integration' to enable integration tests
TWENTY_TEST_MODE=integration

# Optional: prefix for test data
TWENTY_TEST_PREFIX=TEST_PHP_CLIENT_
```

### 3. Obtaining API Credentials

To get your Twenty CRM API token:

1. Log in to your Twenty CRM instance
2. Navigate to Settings → API
3. Create a new API token
4. Copy the token to your `.env` file

**IMPORTANT**: Never commit your `.env` file to version control. It contains sensitive credentials.

## Running Tests

### Run All Unit Tests (Default)

```bash
vendor/bin/phpunit
```

Or explicitly specify the unit test suite:

```bash
vendor/bin/phpunit --testsuite=unit
```

### Run Integration Tests Only

First, ensure your `.env` file is configured with valid credentials and `TWENTY_TEST_MODE=integration`.

```bash
vendor/bin/phpunit --testsuite=integration
```

### Run All Tests (Unit + Integration)

```bash
vendor/bin/phpunit --testsuite=unit,integration
```

### Run Tests with Coverage

```bash
vendor/bin/phpunit --coverage-html coverage/
```

Then open `coverage/index.html` in your browser.

### Run Specific Test File

```bash
vendor/bin/phpunit tests/Unit/DTO/ContactTest.php
```

### Run Specific Test Method

```bash
vendor/bin/phpunit --filter testCreateContact
```

### Run Tests with Verbose Output

```bash
vendor/bin/phpunit --verbose
```

## Integration Tests

### Data Isolation

Integration tests create real data in your Twenty CRM instance. To prevent conflicts and make cleanup easier:

- All test data uses a unique prefix (default: `TEST_PHP_CLIENT_`)
- Each test generates unique identifiers using timestamps
- Tests automatically clean up created resources in `tearDown()`

### Automatic Cleanup

After each integration test:

1. All created resources (contacts, companies) are automatically deleted
2. Cleanup happens even if the test fails
3. Cleanup errors are silently ignored (resource may already be deleted)

### Best Practices for Integration Tests

1. **Use a Test Workspace**: If possible, configure a dedicated test workspace in your Twenty CRM instance
2. **Don't Rely on Existing Data**: Integration tests should create their own test data
3. **Use Unique Identifiers**: Always use the helper methods to generate unique test data
4. **Clean Up**: Always track created resources for cleanup

## Writing Tests

### Writing Unit Tests

Extend `Factorial\TwentyCrm\Tests\TestCase`:

```php
<?php

namespace Factorial\TwentyCrm\Tests\Unit;

use Factorial\TwentyCrm\Tests\TestCase;
use Factorial\TwentyCrm\DTO\Contact;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        // Use factory to create test data
        $contact = ContactFactory::create([
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);

        $this->assertEquals('Test', $contact->getFirstName());
    }
}
```

### Writing Integration Tests

Extend `Factorial\TwentyCrm\Tests\IntegrationTestCase`:

```php
<?php

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\Tests\IntegrationTestCase;
use Factorial\TwentyCrm\DTO\Contact;

class MyIntegrationTest extends IntegrationTestCase
{
    public function testCreateContact(): void
    {
        $this->requireClient();

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('MyTest'),
            lastName: 'Test'
        );

        $created = $this->client->contacts()->create($contact);

        // IMPORTANT: Track for cleanup
        $this->trackResource('contact', $created->getId());

        $this->assertNotNull($created->getId());
    }
}
```

### Helper Methods

#### In `TestCase` (Unit Tests)

- `loadFixture(string $name)` - Load JSON fixture data
- `createTestId()` - Generate unique test identifier
- `getTestPrefix()` - Get test data prefix from environment

#### In `IntegrationTestCase` (Integration Tests)

- `requireClient()` - Ensure client is initialized (use at start of test)
- `trackResource(string $type, string $id)` - Track resource for cleanup
- `generateTestName(string $base)` - Generate unique test name with prefix
- `generateTestEmail()` - Generate unique test email address

### Using Test Factories

The library includes factory classes to easily create test data:

```php
use Factorial\TwentyCrm\Tests\Helpers\ContactFactory;
use Factorial\TwentyCrm\Tests\Helpers\CompanyFactory;

// Create contact with default data
$contact = ContactFactory::create();

// Create contact with custom data
$contact = ContactFactory::create([
    'firstName' => 'Custom',
    'lastName' => 'Name',
]);

// Create contact with unique identifier
$contact = ContactFactory::createUnique('MY_TEST_');

// Create mock API response
$apiResponse = ContactFactory::createApiResponse();
```

## Troubleshooting

### Tests Are Skipped

If you see "Integration tests are disabled", it means:

- You're running integration tests without proper configuration
- Set `TWENTY_TEST_MODE=integration` in your `.env` file
- Ensure `.env` file exists and is readable

### Authentication Errors

If you get 401 authentication errors:

- Verify your `TWENTY_API_TOKEN` is correct
- Check that the token hasn't expired
- Ensure the token has proper permissions

### Connection Errors

If you can't connect to the API:

- Verify `TWENTY_API_BASE_URI` is correct
- Check your network connection
- Ensure the Twenty CRM instance is accessible

### Cleanup Failures

If resources aren't being cleaned up:

- Make sure you're calling `$this->trackResource()` after creating resources
- Check that the API supports DELETE operations
- Verify your API token has delete permissions

### Required Environment Variable Errors

If you see "Required environment variable X is not set":

- Copy `.env.example` to `.env`
- Fill in all required values
- Ensure the `.env` file is in the project root directory

## Continuous Integration

For CI/CD pipelines:

1. **Only run unit tests by default** (they don't require credentials):

```yaml
script:
  - vendor/bin/phpunit --testsuite=unit
```

2. **Optionally run integration tests** if credentials are available:

```yaml
script:
  - vendor/bin/phpunit --testsuite=unit
  - if [ -n "$TWENTY_API_TOKEN" ]; then vendor/bin/phpunit --testsuite=integration; fi
```

3. **Use environment variables** instead of `.env` file in CI:

```yaml
env:
  - TWENTY_API_BASE_URI=https://test-instance.twenty.com/rest/
  - TWENTY_API_TOKEN=$SECURE_TOKEN
  - TWENTY_TEST_MODE=integration
```

## Security Notes

- **Never commit `.env` file** - it's listed in `.gitignore`
- **Never commit API tokens** - use environment variables in CI/CD
- **Use test workspaces** - don't run tests against production data
- **Rotate tokens regularly** - especially if they're exposed

## Questions?

If you encounter issues not covered here, please:

1. Check the [main README](README.md)
2. Review the [Contributing Guide](CONTRIBUTING.md)
3. Open an issue on GitHub
