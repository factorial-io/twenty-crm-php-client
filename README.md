# Twenty CRM PHP Client

A robust PHP client library for interacting with the Twenty CRM API, providing a clean and type-safe interface for customer relationship management operations.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

## Installation

Install the package via Composer:

```bash
composer require factorial-io/twenty-crm-php-client
```

## Quick Start

```php
use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Create client with authentication
$httpClient = new \GuzzleHttp\Client(['base_uri' => 'https://api.twenty.com/rest/']);
$auth = new BearerTokenAuth('your-api-token');
$client = new TwentyCrmClient($httpClient, $auth);

// Search contacts
$filter = new ContactSearchFilter(email: 'john@example.com');
$options = new SearchOptions(limit: 10, orderBy: 'name.firstName');
$contacts = $client->contacts()->find($filter, $options);

// Get specific contact
$contact = $client->contacts()->getById('contact-uuid');
```

## Configuration

### HTTP Client

The client requires a PSR-18 compatible HTTP client. We recommend Guzzle:

```php
use GuzzleHttp\Client;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;

$guzzle = new Client([
    'base_uri' => 'https://api.twenty.com/rest/',
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'My Application/1.0',
    ],
]);

$httpClient = new GuzzleHttpClient($guzzle);
```

### Authentication

Currently supports Bearer Token authentication:

```php
use Factorial\TwentyCrm\Auth\BearerTokenAuth;

$auth = new BearerTokenAuth('your-api-token');
```

### Logging (Optional)

Integrate with any PSR-3 compatible logger:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('twenty-crm');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::INFO));

$client = new TwentyCrmClient($httpClient, $auth, $logger);
```

## Usage

### Contact Management

#### Searching Contacts

```php
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Basic search
$filter = new ContactSearchFilter(email: 'user@example.com');
$contacts = $client->contacts()->find($filter);

// Advanced search with options
$filter = new ContactSearchFilter(
    name: 'John',
    email: 'john@example.com'
);
$options = new SearchOptions(
    limit: 20,
    orderBy: 'createdAt',
    orderDirection: 'DESC'
);
$contacts = $client->contacts()->find($filter, $options);

// Custom filters
$customFilter = new CustomFilter([
    'city' => 'New York',
    'jobTitle' => 'Developer'
]);
$contacts = $client->contacts()->find($customFilter);
```

#### Getting Contact Details

```php
// Get by ID
$contact = $client->contacts()->getById('contact-uuid');

// Get multiple contacts
$contactIds = ['uuid1', 'uuid2', 'uuid3'];
$contacts = $client->contacts()->getByIds($contactIds);
```

### Company Management

```php
use Factorial\TwentyCrm\DTO\CompanySearchFilter;

// Search companies
$filter = new CompanySearchFilter(name: 'Acme Corp');
$companies = $client->companies()->find($filter);

// Get company by ID
$company = $client->companies()->getById('company-uuid');
```

## API Reference

### Client Methods

```php
// Get contacts service
$contactService = $client->contacts();

// Get companies service  
$companyService = $client->companies();
```

### Search Filters

#### ContactSearchFilter

```php
$filter = new ContactSearchFilter(
    name?: string,           // Contact name
    email?: string,          // Email address
    phone?: string,          // Phone number
    companyId?: string       // Associated company ID
);
```

#### CompanySearchFilter

```php
$filter = new CompanySearchFilter(
    name?: string,           // Company name
    domain?: string,         // Company domain
    industry?: string        // Industry type
);
```

#### CustomFilter

```php
$filter = new CustomFilter([
    'field_name' => 'value',
    'another_field' => 'another_value'
]);
```

### Search Options

```php
$options = new SearchOptions(
    limit?: int,             // Maximum results (default: 50)
    offset?: int,            // Results offset (default: 0)
    orderBy?: string,        // Field to order by
    orderDirection?: string  // 'ASC' or 'DESC' (default: 'ASC')
);
```

## Error Handling

The client provides comprehensive error handling:

```php
use Factorial\TwentyCrm\Exception\TwentyCrmException;
use Factorial\TwentyCrm\Exception\AuthenticationException;
use Factorial\TwentyCrm\Exception\ApiException;

try {
    $contacts = $client->contacts()->find($filter);
} catch (AuthenticationException $e) {
    // Handle authentication errors
    error_log('Authentication failed: ' . $e->getMessage());
} catch (ApiException $e) {
    // Handle API-specific errors
    error_log('API error: ' . $e->getMessage());
    error_log('HTTP status: ' . $e->getStatusCode());
} catch (TwentyCrmException $e) {
    // Handle general client errors
    error_log('Client error: ' . $e->getMessage());
}
```

## Requirements

- **PHP**: 8.1 or higher
- **HTTP Client**: PSR-18 compatible HTTP client implementation
- **Logger**: PSR-3 logger (optional)

## Features

- Type Safety: Full type hints and strict typing
- PSR Compliance: Follows PSR-18 (HTTP Client) and PSR-3 (Logger) standards
- Framework Agnostic: Works with any PHP framework or vanilla PHP
- Comprehensive Error Handling: Detailed exception hierarchy
- Flexible Filtering: Support for custom search filters
- Pagination: Built-in pagination support
- Logging: Optional PSR-3 compatible logging
- Contact Management: Full contact/people API support
- Company Management: Company search and retrieval
- Extensible: Easy to extend for additional endpoints

## Testing

This library includes a comprehensive test suite with both unit and integration tests.

### Running Unit Tests (No Credentials Required)

```bash
# Run all unit tests
vendor/bin/phpunit

# Or explicitly
vendor/bin/phpunit --testsuite=unit
```

Unit tests use mocked API responses and don't require any credentials or API access.

### Running Integration Tests (Requires Credentials)

Integration tests run against a real Twenty CRM backend:

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Add your Twenty CRM credentials to `.env`:
```bash
TWENTY_API_BASE_URI=https://your-instance.twenty.com/rest/
TWENTY_API_TOKEN=your_api_token_here
TWENTY_TEST_MODE=integration
```

3. Run integration tests:
```bash
vendor/bin/phpunit --testsuite=integration
```

**Note**: Integration tests create and delete real data in your Twenty CRM instance. Use a test workspace if possible.

For detailed testing documentation, see [TESTING.md](TESTING.md).

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Setup

```bash
# Clone repository
git clone git@github.com:factorial-io/twenty-crm-php-client.git
cd twenty-crm-php-client

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.