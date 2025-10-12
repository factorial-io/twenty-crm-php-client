# Twenty CRM PHP Client

A powerful and flexible PHP client library for interacting with the Twenty CRM API. Unlike traditional CRM clients with hardcoded entities, this library uses a **dynamic entity system** and **code generation** to adapt to any Twenty CRM configuration—including custom entities and custom fields.

## Table of Contents

- [What's New in v0.4](#whats-new-in-v04)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Architecture](#architecture)
- [Usage](#usage)
  - [Option 1: Code Generation (Recommended)](#option-1-code-generation-recommended)
  - [Option 2: Dynamic Entities (Flexible)](#option-2-dynamic-entities-flexible)
- [Entity Relations](#entity-relations)
- [Working with Custom Entities](#working-with-custom-entities)
- [Configuration](#configuration)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)
- [Security](#security)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Migration from v0.3](#migration-from-v03)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

## What's New in v0.4

v0.4 represents a major refactoring with breaking changes:

- **Namespace Reorganization**: Classes moved to dedicated namespaces for better organization
  - Filters: `DTO\` → `Query\` (CustomFilter, FilterBuilder)
  - Entities: `DTO\` → `Entity\` (DynamicEntity)
  - Services extracted to `Services\` namespace
  - Registry extracted to `Registry\` namespace
  - Collections organized in `Collection\` namespace
- **Dynamic Entity System**: Work with any Twenty CRM entity without hardcoded classes
- **Code Generation**: Generate typed entities, services, and collections from your schema
- **Enhanced Type Safety**: PHPStan level 5 compliance with PHP 8.2+ support
- **Improved Error Handling**: Dedicated exception hierarchy
- **Metadata-Driven Architecture**: Automatically adapts to your Twenty CRM schema

**Migration Required**: See [Migration from v0.3](#migration-from-v03) for upgrade instructions.

## Features

✅ **Dynamic Entity System**: Works with any Twenty CRM entity without code changes
✅ **Code Generation**: Generate typed DTOs, services, and collections for your schema
✅ **Custom Entity Support**: Campaign, Opportunity, or any custom entity works immediately
✅ **Entity Relations**: Lazy and eager loading of related entities (Person ↔ Company, etc.)
✅ **Type Safety**: Full PHP 8.2+ type hints with PHPStan level 5 compliance
✅ **Complex Field Handling**: Automatic transformation of phones, emails, addresses, links
✅ **PSR Compliant**: Follows PSR-18 (HTTP Client) and PSR-3 (Logger) standards
✅ **Framework Agnostic**: Works with any PHP framework or vanilla PHP
✅ **Comprehensive Testing**: Unit and integration test suites included

## Requirements

- **PHP**: 8.2 or higher
- **Extensions**: `json`, `mbstring`
- **HTTP Client**: PSR-18 compatible (Guzzle 7+ recommended)
- **Twenty CRM**: API access with valid authentication token

**Optional:**
- PSR-3 Logger for debugging (Monolog recommended)

## Installation

Install via Composer:

```bash
composer require factorial-io/twenty-crm-php-client
```

## Quick Start

```php
use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Setup HTTP client
$guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://api.twenty.com/rest/']);
$httpFactory = new \GuzzleHttp\Psr7\HttpFactory();
$streamFactory = new \GuzzleHttp\Psr7\HttpFactory();
$auth = new BearerTokenAuth('your-api-token');

$httpClient = new GuzzleHttpClient(
    $guzzle,
    $httpFactory,
    $streamFactory,
    $auth,
    'https://api.twenty.com/rest/'
);

// Create client
$client = new TwentyCrmClient($httpClient);

// Work with any entity dynamically
$filter = new CustomFilter('name.firstName eq "John"');
$options = new SearchOptions(limit: 10);
$persons = $client->entity('person')->find($filter, $options);

foreach ($persons as $person) {
    echo $person->get('name')['firstName'] . "\n";
}
```

## Project Structure

The library is organized into dedicated namespaces for better separation of concerns:

```
Factorial\TwentyCrm\
├── Auth\              # Authentication classes (BearerTokenAuth)
├── Client\            # Main client interface (TwentyCrmClient)
├── Collection\        # Entity collection classes (DynamicEntityCollection)
├── Console\           # CLI commands (code generation)
├── DTO\               # Data transfer objects (Name, Email, Phone, SearchOptions, etc.)
├── Entity\            # Entity classes (DynamicEntity)
├── Enums\             # Enumerations (FieldType, RelationType)
├── Exception\         # Custom exceptions (TwentyCrmException, ApiException)
├── FieldHandlers\     # Field type handlers (transformation logic)
├── Generator\         # Code generation classes
├── Http\              # HTTP client implementation (GuzzleHttpClient)
├── Metadata\          # Entity and field metadata (EntityDefinition, FieldMetadata)
├── Query\             # Filter and query builders (CustomFilter, FilterBuilder)
├── Registry\          # Entity registry (EntityRegistry)
└── Services\          # Service classes (GenericEntityService, MetadataService)
```

### Namespace Quick Reference

| Component | Namespace | Example Classes |
|-----------|-----------|-----------------|
| Queries & Filters | `Factorial\TwentyCrm\Query\` | `CustomFilter`, `FilterBuilder` |
| Entities | `Factorial\TwentyCrm\Entity\` | `DynamicEntity` |
| Services | `Factorial\TwentyCrm\Services\` | `GenericEntityService`, `MetadataService` |
| Registry | `Factorial\TwentyCrm\Registry\` | `EntityRegistry` |
| Collections | `Factorial\TwentyCrm\Collection\` | `DynamicEntityCollection` |
| DTOs | `Factorial\TwentyCrm\DTO\` | `Name`, `Email`, `Phone`, `SearchOptions` |
| Metadata | `Factorial\TwentyCrm\Metadata\` | `EntityDefinition`, `FieldMetadata` |
| Authentication | `Factorial\TwentyCrm\Auth\` | `BearerTokenAuth` |
| HTTP | `Factorial\TwentyCrm\Http\` | `GuzzleHttpClient` |
| Exceptions | `Factorial\TwentyCrm\Exception\` | `TwentyCrmException`, `ApiException` |

## Architecture

The library follows a layered architecture:

```
┌─────────────────────────────────────────┐
│   TwentyCrmClient (Entry Point)         │
├─────────────────────────────────────────┤
│   Services Layer                        │
│   - GenericEntityService                │
│   - MetadataService                     │
├─────────────────────────────────────────┤
│   Registry & Metadata                   │
│   - EntityRegistry                      │
│   - EntityDefinition                    │
├─────────────────────────────────────────┤
│   Query & Filter Layer                  │
│   - FilterBuilder                       │
│   - CustomFilter                        │
├─────────────────────────────────────────┤
│   HTTP Layer                            │
│   - GuzzleHttpClient (PSR-18)          │
│   - BearerTokenAuth                     │
├─────────────────────────────────────────┤
│   Twenty CRM REST API                   │
└─────────────────────────────────────────┘
```

**Key Concepts**:

- **Dynamic Entities**: Work with any entity without hardcoded classes
- **Metadata-Driven**: Automatically adapts to your Twenty CRM schema
- **Code Generation**: Optional typed entities for better developer experience
- **PSR Standards**: Follows PSR-3 (logging) and PSR-18 (HTTP client)

## Usage

The library offers **two approaches** for working with Twenty CRM entities:

### Option 1: Code Generation (Recommended)

Generate fully-typed entity classes for your Twenty CRM instance.

**Advantages:**
- ✅ Full IDE autocomplete support
- ✅ Type safety with PHPStan/Psalm
- ✅ Compile-time error checking
- ✅ Familiar object-oriented API

#### Step 1: Create Configuration File

Create `.twenty-codegen.yaml`:

```yaml
namespace: MyApp\TwentyCrm\Entities
output_dir: src/TwentyCrm/Entities
api_url: https://your-twenty.example.com/rest/
api_token: ${TWENTY_API_TOKEN}
entities:
  - person
  - company
  - campaign  # Works with custom entities!
options:
  overwrite: true
```

#### Step 2: Generate Entities

```bash
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --with-services --with-collections
```

This generates:
```
src/TwentyCrm/Entities/
├── Person.php
├── PersonService.php
├── PersonCollection.php
├── Company.php
├── CompanyService.php
├── CompanyCollection.php
├── Campaign.php
├── CampaignService.php
└── CampaignCollection.php
```

#### Step 3: Use Generated Entities

```php
use MyApp\TwentyCrm\Entities\Person;
use MyApp\TwentyCrm\Entities\PersonService;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Create person service
$personService = new PersonService(
    $client->getHttpClient(),
    $client->registry()->getDefinition('person')
);

// Search persons
$filter = new CustomFilter('emails.primaryEmail eq "john@example.com"');
$options = new SearchOptions(limit: 10);
$persons = $personService->find($filter, $options);

// Create person
$person = new Person($client->registry()->getDefinition('person'));
$person->setEmail('john@example.com');
$person->setName(new Name('John', 'Doe'));
$person->setJobTitle('Developer');
$created = $personService->create($person);

// Update person
$person = $personService->getById($created->getId());
$person->setEmail('john.doe@example.com');
$personService->update($person);

// Get with relations
$options = new SearchOptions(limit: 10, with: ['company']);
$persons = $personService->find($filter, $options);

foreach ($persons as $person) {
    $company = $person->getRelation('company');
    if ($company) {
        echo "{$person->getName()->getFullName()} works at {$company->get('name')}\n";
    }
}
```

### Option 2: Dynamic Entities (Flexible)

Work with entities dynamically without code generation.

**Advantages:**
- ✅ No code generation step required
- ✅ Works with any entity immediately
- ✅ Adapts automatically to schema changes
- ✅ Perfect for rapid prototyping

**Example:**

```php
use Factorial\TwentyCrm\Entity\DynamicEntity;
use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Get entity definition from registry
$definition = $client->registry()->getDefinition('person');

// Create person
$person = new DynamicEntity($definition, [
    'emails' => ['primaryEmail' => 'john@example.com'],
    'name' => ['firstName' => 'John', 'lastName' => 'Doe'],
    'jobTitle' => 'Developer'
]);

$created = $client->entity('person')->create($person);
echo "Created person: " . $created->getId() . "\n";

// Search persons
$filter = new CustomFilter('jobTitle eq "Developer"');
$options = new SearchOptions(limit: 10, orderBy: 'createdAt');
$persons = $client->entity('person')->find($filter, $options);

foreach ($persons as $person) {
    echo $person->get('name')['firstName'] . " - " . $person->get('jobTitle') . "\n";
}

// Update person
$person = $client->entity('person')->getById($created->getId());
$person->set('jobTitle', 'Senior Developer');
$client->entity('person')->update($person);

// Delete person
$client->entity('person')->delete($created->getId());
```

## Entity Relations

The library supports lazy and eager loading of related entities.

### Lazy Loading (On Demand)

```php
// Load person
$person = $client->entity('person')->getById('person-123');

// Load related company (triggers API call)
$company = $person->loadRelation('company');
echo "Works at: " . $company->get('name') . "\n";

// Load related activities
$activities = $person->loadRelation('activities');
foreach ($activities as $activity) {
    echo $activity->get('title') . "\n";
}
```

### Eager Loading (Batch)

```php
use Factorial\TwentyCrm\DTO\SearchOptions;

// Load persons with company relation preloaded
$options = new SearchOptions(
    limit: 20,
    with: ['company', 'activities']  // Preload relations
);

$persons = $client->entity('person')->find($filter, $options);

foreach ($persons as $person) {
    // No additional API call - already loaded
    $company = $person->getRelation('company');
    if ($company) {
        echo "{$person->get('name')['firstName']} works at {$company->get('name')}\n";
    }
}
```

### Relation Types

The library automatically discovers and supports all relation types:

- **MANY_TO_ONE**: Person → Company
- **ONE_TO_MANY**: Company → People
- **MANY_TO_MANY**: Campaign ↔ People
- **ONE_TO_ONE**: Person → Profile

```php
// MANY_TO_ONE: Get person's company
$person = $client->entity('person')->getById('person-123');
$company = $person->loadRelation('company');

// ONE_TO_MANY: Get company's people
$company = $client->entity('company')->getById('company-456');
$people = $company->loadRelation('people'); // Returns array
```

## Working with Custom Entities

The library works seamlessly with custom entities without any code changes.

### Example: Campaign Entity

```php
// Works immediately - no configuration needed!
$definition = $client->registry()->getDefinition('campaign');

$campaign = new DynamicEntity($definition, [
    'name' => 'Q1 2025 Product Launch',
    'status' => 'ACTIVE',
    'startDate' => '2025-01-01',
    'budget' => 50000
]);

$created = $client->entity('campaign')->create($campaign);

// Search campaigns
$filter = new CustomFilter('status eq "ACTIVE"');
$campaigns = $client->entity('campaign')->find($filter);

foreach ($campaigns as $campaign) {
    echo $campaign->get('name') . " - Budget: $" . $campaign->get('budget') . "\n";
}

// Load campaign participants
$participants = $campaign->loadRelation('people');
echo "Participants: " . count($participants) . "\n";
```

### Generate Code for Custom Entities

```bash
# Add custom entity to config
echo "  - campaign" >> .twenty-codegen.yaml

# Generate typed class
vendor/bin/twenty-generate --config=.twenty-codegen.yaml

# Now use with full type safety
use MyApp\TwentyCrm\Entities\Campaign;
use MyApp\TwentyCrm\Entities\CampaignService;

$campaign = new Campaign($definition);
$campaign->setName('Q1 Launch');  // IDE autocomplete!
```

## Configuration

### HTTP Client Setup

```php
use GuzzleHttp\Client;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;

$guzzle = new Client([
    'base_uri' => 'https://api.twenty.com/rest/',
    'timeout' => 30,
    'headers' => ['User-Agent' => 'MyApp/1.0'],
]);

$httpFactory = new \GuzzleHttp\Psr7\HttpFactory();
$streamFactory = new \GuzzleHttp\Psr7\HttpFactory();
$auth = new BearerTokenAuth('your-api-token');

$httpClient = new GuzzleHttpClient(
    $guzzle,
    $httpFactory,
    $streamFactory,
    $auth,
    'https://api.twenty.com/rest/'
);
```

### Authentication

```php
use Factorial\TwentyCrm\Auth\BearerTokenAuth;

$auth = new BearerTokenAuth('your-api-token');
```

### Logging

The library supports PSR-3 logging for debugging and monitoring API interactions.

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use Factorial\TwentyCrm\Client\TwentyCrmClient;

// Create logger
$logger = new Logger('twenty-crm');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new StreamHandler('logs/twenty-crm.log', Logger::INFO));

// Pass logger to HTTP client
$httpClient = new GuzzleHttpClient(
    $guzzle,
    $httpFactory,
    $streamFactory,
    $auth,
    'https://api.twenty.com/rest/',
    $logger  // Logger automatically logs all HTTP requests/responses
);

// Pass logger to client (propagates to all services)
$client = new TwentyCrmClient($httpClient, $logger);

// All operations are now logged
$persons = $client->entity('person')->find($filter, $options);
```

**Log Levels:**
- **DEBUG**: API requests/responses, entity operations, service initialization
- **ERROR**: Authentication failures, API errors, network errors

**Example Log Output:**
```
[DEBUG] Twenty CRM client initialized
[DEBUG] Creating entity service {"entity":"person"}
[DEBUG] Finding entities {"entity":"people","filter":"...","options":{...}}
[DEBUG] Twenty CRM API request {"method":"GET","url":"https://...","body":null}
[DEBUG] Twenty CRM API response {"status":200,"body":"..."}
[DEBUG] Found entities {"entity":"people","count":15}
```

## API Reference

### Client Methods

```php
$client = new TwentyCrmClient($httpClient);

// Get generic entity service for any entity
$service = $client->entity('person');      // Returns Services\GenericEntityService
$service = $client->entity('company');     // Returns Services\GenericEntityService
$service = $client->entity('campaign');    // Works with custom entities!

// Access entity registry (metadata)
$registry = $client->registry();           // Returns Registry\EntityRegistry
$definition = $registry->getDefinition('person');  // Returns Metadata\EntityDefinition
$allEntities = $registry->getAllEntityNames();

// Access metadata service
$metadata = $client->metadata();           // Returns Services\MetadataService
$fields = $metadata->getFieldsMetadata('person');
```

### GenericEntityService Methods

```php
$service = $client->entity('person');

// Find entities
$entities = $service->find($filter, $options);  // DynamicEntityCollection

// Get by ID
$entity = $service->getById('uuid');            // DynamicEntity|null

// Create entity
$created = $service->create($entity);           // DynamicEntity

// Update entity
$updated = $service->update($entity);           // DynamicEntity

// Delete entity
$success = $service->delete('uuid');            // bool

// Batch upsert
$results = $service->batchUpsert([$entity1, $entity2]); // array
```

### Search Filters

#### FilterBuilder (Recommended)

Use the composable FilterBuilder for type-safe, validated filters:

```php
use Factorial\TwentyCrm\Query\FilterBuilder;

// Simple filter
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')
    ->build();

// Multiple conditions (AND)
$filter = FilterBuilder::create()
    ->equals('status', 'ACTIVE')
    ->greaterThan('createdAt', '2025-01-01')
    ->contains('emails.primaryEmail', '@example.com')
    ->build();

// Multiple conditions (OR)
$filter = FilterBuilder::create()
    ->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING')
    ->build();

// With validation (validates against entity metadata)
$definition = $client->registry()->getDefinition('person');
$filter = FilterBuilder::forEntity($definition)
    ->equals('status', 'ACTIVE')  // Validates enum values
    ->build();

// Helper methods
$filter = FilterBuilder::create()
    ->equals('name', 'John')              // eq
    ->notEquals('status', 'DELETED')      // neq
    ->greaterThan('age', 18)              // gt
    ->greaterThanOrEquals('age', 21)      // gte
    ->lessThan('salary', 100000)          // lt
    ->lessThanOrEquals('salary', 50000)   // lte
    ->in('status', ['ACTIVE', 'PENDING']) // in array
    ->contains('email', '@example.com')   // substring
    ->startsWith('name', 'Jo')            // prefix
    ->isNull('deletedAt')                 // is NULL
    ->isNotNull('email')                  // isNot NULL
    ->build();
```

#### CustomFilter (Advanced)

For direct filter string control:

```php
use Factorial\TwentyCrm\Query\CustomFilter;

// String filter (Twenty CRM filter syntax)
$filter = new CustomFilter('name.firstName eq "John"');
$filter = new CustomFilter('emails.primaryEmail contains "@example.com"');
$filter = new CustomFilter('createdAt gt "2025-01-01"');

// Complex filters
$filter = new CustomFilter('status eq "ACTIVE" and budget gt 10000');
```

**See [docs/FILTERS.md](docs/FILTERS.md) for complete filter documentation.**

### Search Options

```php
use Factorial\TwentyCrm\DTO\SearchOptions;

$options = new SearchOptions(
    limit: 20,                    // Max results
    offset: 0,                    // Pagination offset
    orderBy: 'createdAt',         // Order by field
    orderDirection: 'DESC',       // ASC or DESC
    with: ['company', 'activities'] // Eager load relations
);
```

### DynamicEntity Methods

```php
$entity = new DynamicEntity($definition, $data);

// Field access
$value = $entity->get('fieldName');            // Get field value
$entity->set('fieldName', $value);             // Set field value
$entity->has('fieldName');                     // Check if field exists
$entity->unset('fieldName');                   // Remove field

// Array access (alternative syntax)
$value = $entity['fieldName'];
$entity['fieldName'] = $value;

// Relations
$related = $entity->loadRelation('relationName');     // Lazy load
$related = $entity->getRelation('relationName');      // Get if loaded
$hasRelation = $entity->hasLoadedRelation('name');    // Check loaded
$entity->setRelation('relationName', $related);       // Set relation

// Serialization
$array = $entity->toArray();                   // Export to array
$json = json_encode($entity);                  // JSON serializable

// Iteration
foreach ($entity as $field => $value) {
    echo "$field: $value\n";
}
```

## Error Handling

```php
use Factorial\TwentyCrm\Exception\TwentyCrmException;
use Factorial\TwentyCrm\Exception\AuthenticationException;
use Factorial\TwentyCrm\Exception\ApiException;

try {
    $person = $client->entity('person')->getById($id);
} catch (AuthenticationException $e) {
    // Handle authentication errors (401, 403)
    error_log('Authentication failed: ' . $e->getMessage());
} catch (ApiException $e) {
    // Handle API errors (400, 404, 500, etc.)
    error_log('API error: ' . $e->getMessage());
    error_log('Status code: ' . $e->getStatusCode());
} catch (TwentyCrmException $e) {
    // Handle general client errors
    error_log('Client error: ' . $e->getMessage());
}
```

## Security

### API Token Storage

**Never commit your API tokens to version control**. Use environment variables or secure configuration management:

```php
// ✅ Good: Use environment variables
$token = getenv('TWENTY_API_TOKEN');
if (!$token) {
    throw new \RuntimeException('TWENTY_API_TOKEN environment variable is not set');
}
$auth = new BearerTokenAuth($token);

// ✅ Good: Use .env files (with vlucas/phpdotenv)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$auth = new BearerTokenAuth($_ENV['TWENTY_API_TOKEN']);

// ❌ Bad: Hardcoded token
$auth = new BearerTokenAuth('your-secret-token-here');
```

**Important**: Add `.env` files to your `.gitignore`:
```
.env
.env.local
*.token
credentials.json
```

### HTTPS Only

The library automatically uses HTTPS for all API requests. Always use HTTPS endpoints for production:

```php
// ✅ Good: HTTPS endpoint
$baseUri = 'https://your-instance.twenty.com/rest/';

// ❌ Bad: HTTP endpoint (insecure)
$baseUri = 'http://your-instance.twenty.com/rest/';
```

### Error Message Handling

Be cautious when logging or displaying error messages in production, as they may contain sensitive information:

```php
try {
    $person = $client->entity('person')->getById($id);
} catch (TwentyCrmException $e) {
    // ✅ Good: Log to secure location
    error_log('Twenty CRM error: ' . $e->getMessage());

    // ❌ Bad: Display detailed error to end user
    echo 'Error: ' . $e->getMessage();  // May leak API details

    // ✅ Good: Generic error message to user
    echo 'An error occurred while fetching data. Please try again later.';
}
```

### Multi-Tenancy

For multi-tenant applications, create separate client instances with different credentials:

```php
function getTwentyCrmClient(string $tenantId): TwentyCrmClient
{
    $token = getTenantApiToken($tenantId);  // Fetch from secure storage
    $baseUri = getTenantApiUrl($tenantId);

    $auth = new BearerTokenAuth($token);
    $httpClient = new GuzzleHttpClient(
        new \GuzzleHttp\Client(['base_uri' => $baseUri]),
        $httpFactory,
        $streamFactory,
        $auth,
        $baseUri
    );

    return new TwentyCrmClient($httpClient);
}
```

## Testing

### Running Unit Tests (No Credentials Required)

```bash
vendor/bin/phpunit tests/Unit
```

Unit tests use mocked API responses and don't require credentials.

### Running Integration Tests (Requires Credentials)

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Add your Twenty CRM credentials:
   ```env
   TWENTY_API_BASE_URI=https://your-instance.twenty.com/rest/
   TWENTY_API_TOKEN=your_api_token_here
   ```

3. Run integration tests:
   ```bash
   vendor/bin/phpunit tests/Integration
   ```

**Note:** Integration tests create and delete real data. Use a test workspace if possible.

For detailed testing documentation, see [TESTING.md](TESTING.md).

## Troubleshooting

### Namespace Issues After Upgrading to v0.4

**Issue**: `Class 'Factorial\TwentyCrm\DTO\CustomFilter' not found`

**Solution**: Update your imports to use the new namespace structure:

```php
// ❌ Old (v0.3 and earlier)
use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\DTO\DynamicEntity;

// ✅ New (v0.4+)
use Factorial\TwentyCrm\Query\CustomFilter;      // Moved to Query namespace
use Factorial\TwentyCrm\Query\FilterBuilder;     // Moved to Query namespace
use Factorial\TwentyCrm\Entity\DynamicEntity;    // Moved to Entity namespace
```

**Note**: Most DTOs (Name, Email, Phone, Address, Currency) remain in the `DTO` namespace. Only query-related and entity classes moved.

### Code Generation Not Finding Entities

**Issue**: `Entity 'campaign' not found in metadata`

**Solution**: Ensure your API token has access to the entity and that the entity exists:

```bash
# Test API access
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-instance.twenty.com/rest/metadata/objects

# Verify entity name is correct (case-sensitive)
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --entity=campaign
```

### Authentication Errors

**Issue**: `Authentication failed: 401 Unauthorized`

**Solutions**:

1. **Verify token is correct**:
   ```bash
   echo $TWENTY_API_TOKEN
   ```

2. **Check token hasn't expired**: Generate a new token in Twenty CRM settings

3. **Verify base URI includes `/rest/`**:
   ```php
   // ✅ Correct
   $baseUri = 'https://your-instance.twenty.com/rest/';

   // ❌ Missing /rest/
   $baseUri = 'https://your-instance.twenty.com/';
   ```

### Filter Syntax Errors

**Issue**: `Invalid filter syntax`

**Solution**: Use `FilterBuilder` for validated filters:

```php
// ❌ Error-prone string filters
$filter = new CustomFilter('name.firstName = "John"');  // Wrong operator

// ✅ Use FilterBuilder for validation
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')  // Correct operator (eq)
    ->build();
```

For complex filters, see [docs/FILTERS.md](docs/FILTERS.md).

### Relation Loading Issues

**Issue**: `Relation 'company' not found`

**Solutions**:

1. **Check relation name**: Use exact field name from metadata:
   ```php
   // Check available relations
   $definition = $client->registry()->getDefinition('person');
   $relations = array_filter($definition->getFields(), fn($f) => $f->isRelation());
   ```

2. **Use eager loading** to avoid N+1 queries:
   ```php
   $options = new SearchOptions(with: ['company', 'activities']);
   $persons = $service->find($filter, $options);
   ```

### PSR-18 HTTP Client Issues

**Issue**: `No PSR-18 HTTP client found`

**Solution**: Install Guzzle or another PSR-18 compatible client:

```bash
composer require guzzlehttp/guzzle
```

### PHP Version Compatibility

**Issue**: `Parse error: syntax error, unexpected ':'`

**Solution**: This library requires PHP 8.2+. Check your PHP version:

```bash
php -v
```

If you're on PHP 8.1 or lower, upgrade to PHP 8.2 or higher.

## FAQ

### General Questions

**Q: Do I need to use code generation?**

A: No. The dynamic entity system works without code generation. Use code generation for better IDE support, autocomplete, and type safety.

**Q: Can I use this with custom entities?**

A: Yes! The library automatically discovers all entities (standard and custom) from your Twenty CRM instance. Just use `$client->entity('your-custom-entity')`.

**Q: Does this support multi-tenancy?**

A: Yes. Create separate `TwentyCrmClient` instances for each tenant with different API tokens and base URLs. See the [Security](#security) section for an example.

**Q: How do I debug API calls?**

A: Enable logging by passing a PSR-3 logger to the client. See the [Logging](#logging) section for details.

### Filters and Queries

**Q: What's the difference between FilterBuilder and CustomFilter?**

A: `FilterBuilder` provides type-safe filter construction with validation against your entity metadata. `CustomFilter` allows direct filter strings for advanced use cases. Use `FilterBuilder` unless you need specific filter syntax.

**Q: Can I use complex boolean logic (AND/OR)?**

A: Yes. Use `FilterBuilder`:
```php
// AND (default)
$filter = FilterBuilder::create()
    ->equals('status', 'ACTIVE')
    ->greaterThan('age', 18)
    ->build();

// OR
$filter = FilterBuilder::create()
    ->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING')
    ->build();
```

**Q: How do I filter by date ranges?**

A: Use `greaterThan`/`lessThan` with ISO date strings:
```php
$filter = FilterBuilder::create()
    ->greaterThanOrEquals('createdAt', '2025-01-01')
    ->lessThan('createdAt', '2025-02-01')
    ->build();
```

### Relations and Data Loading

**Q: When should I use eager loading vs lazy loading?**

A:
- **Eager loading** (preferred): When you know you'll need relations. Loads all data in one request.
  ```php
  $options = new SearchOptions(with: ['company']);
  ```
- **Lazy loading**: For on-demand loading. Makes separate API calls per relation.
  ```php
  $company = $person->loadRelation('company');
  ```

**Q: Can I load nested relations (e.g., person → company → industry)?**

A: Not directly. Load relations in sequence:
```php
$person = $client->entity('person')->getById($id);
$company = $person->loadRelation('company');
$industry = $company->loadRelation('industry');
```

### Code Generation

**Q: Do I need to regenerate entities when my schema changes?**

A: Yes. Regenerate entities whenever you add/remove fields or entities in Twenty CRM:
```bash
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --overwrite
```

**Q: Can I customize generated entity classes?**

A: Generated classes shouldn't be manually edited (they'll be overwritten). Instead, extend them:
```php
namespace MyApp\Custom;

use MyApp\TwentyCrm\Entities\Person as BasePerson;

class Person extends BasePerson
{
    public function getFullName(): string
    {
        return $this->getName()->getFullName();
    }
}
```

**Q: Can I generate entities for all entities at once?**

A: Yes. Omit the `entities` key in your config, or use `--all`:
```yaml
# .twenty-codegen.yaml
namespace: MyApp\TwentyCrm\Entities
output_dir: src/TwentyCrm/Entities
api_url: https://your-instance.twenty.com/rest/
api_token: ${TWENTY_API_TOKEN}
# No 'entities' key = generate all
```

### Performance and Best Practices

**Q: How do I avoid N+1 query problems?**

A: Always use eager loading when you know you'll need relations:
```php
// ❌ N+1 problem (1 query + N relation queries)
$persons = $service->find($filter);
foreach ($persons as $person) {
    $company = $person->loadRelation('company');  // N queries
}

// ✅ Eager loading (1 or 2 queries total)
$options = new SearchOptions(with: ['company']);
$persons = $service->find($filter, $options);
foreach ($persons as $person) {
    $company = $person->getRelation('company');  // No additional query
}
```

**Q: Should I cache entity definitions?**

A: The `EntityRegistry` automatically caches definitions in memory during a request. For long-running processes (workers, daemons), consider invalidating the cache periodically.

**Q: What's the recommended pagination approach?**

A: Use `limit` and `offset` with `SearchOptions`:
```php
$perPage = 50;
$page = 1;

$options = new SearchOptions(
    limit: $perPage,
    offset: ($page - 1) * $perPage,
    orderBy: 'createdAt',
    orderDirection: 'DESC'
);
```

## Migration from v0.3

**v0.4 introduces breaking changes.** The hardcoded `Contact` and `Company` classes have been removed in favor of the dynamic entity system.

**See [MIGRATION.md](MIGRATION.md) for a comprehensive migration guide.**

### Quick Migration Summary

**Before (v0.3 and earlier):**
```php
use Factorial\TwentyCrm\DTO\Contact;
$contacts = $client->contacts()->find($filter);
```

**After (v0.4 with code generation):**
```php
use MyApp\TwentyCrm\Entities\PersonService;
$personService = new PersonService($client->getHttpClient(), $definition);
$persons = $personService->find($filter);
```

**After (v0.4 with dynamic entities):**
```php
$persons = $client->entity('person')->find($filter);
```

## Code Generation

For detailed code generation documentation, including configuration options and advanced usage, see the code generation section above or run:

```bash
vendor/bin/twenty-generate --help
```

### Code Generation Configuration

**YAML Configuration** (`.twenty-codegen.yaml`):

```yaml
namespace: MyApp\TwentyCrm\Entities
output_dir: src/TwentyCrm/Entities
api_url: https://twenty.example.com/rest/
api_token: ${TWENTY_API_TOKEN}
entities:
  - person
  - company
  - campaign
  - opportunity
options:
  overwrite: true
```

### Generation Command

```bash
# Basic generation
vendor/bin/twenty-generate --config=.twenty-codegen.yaml

# Generate with services and collections
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --with-services --with-collections

# Generate specific entity
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --entity=campaign

# Override options
vendor/bin/twenty-generate --namespace="Custom\\Namespace" --output=custom/path
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Setup

```bash
git clone git@github.com:factorial-io/twenty-crm-php-client.git
cd twenty-crm-php-client
composer install
vendor/bin/phpunit tests/Unit
```

### Code Quality

```bash
# Run PHPStan (level 5)
vendor/bin/phpstan analyse src

# Run PHPCS (PSR-12)
vendor/bin/phpcs src

# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix
```

## Changelog

For a detailed history of changes, see [CHANGELOG.md](CHANGELOG.md).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Questions or Issues?**
- Report bugs: [GitHub Issues](https://github.com/factorial-io/twenty-crm-php-client/issues)
- Documentation: [README.md](README.md) | [MIGRATION.md](MIGRATION.md) | [TESTING.md](TESTING.md)
