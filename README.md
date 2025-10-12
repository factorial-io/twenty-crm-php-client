# Twenty CRM PHP Client

A powerful and flexible PHP client library for interacting with the Twenty CRM API. Unlike traditional CRM clients with hardcoded entities, this library uses a **dynamic entity system** and **code generation** to adapt to any Twenty CRM configuration—including custom entities and custom fields.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [Option 1: Code Generation (Recommended)](#option-1-code-generation-recommended)
  - [Option 2: Dynamic Entities (Flexible)](#option-2-dynamic-entities-flexible)
- [Entity Relations](#entity-relations)
- [Working with Custom Entities](#working-with-custom-entities)
- [Configuration](#configuration)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Migration from v0.x](#migration-from-v0x)
- [Contributing](#contributing)
- [License](#license)

## Features

✅ **Dynamic Entity System**: Works with any Twenty CRM entity without code changes
✅ **Code Generation**: Generate typed DTOs, services, and collections for your schema
✅ **Custom Entity Support**: Campaign, Opportunity, or any custom entity works immediately
✅ **Entity Relations**: Lazy and eager loading of related entities (Person ↔ Company, etc.)
✅ **Type Safety**: Full PHP 8.1+ type hints with PHPStan level 5 compliance
✅ **Complex Field Handling**: Automatic transformation of phones, emails, addresses, links
✅ **PSR Compliant**: Follows PSR-18 (HTTP Client) and PSR-3 (Logger) standards
✅ **Framework Agnostic**: Works with any PHP framework or vanilla PHP
✅ **Comprehensive Testing**: Unit and integration test suites included

## Installation

Install via Composer:

```bash
composer require factorial-io/twenty-crm-php-client
```

**Requirements:**
- PHP 8.1 or higher
- PSR-18 compatible HTTP client (e.g., Guzzle)

## Quick Start

```php
use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use Factorial\TwentyCrm\DTO\CustomFilter;
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
use Factorial\TwentyCrm\DTO\CustomFilter;
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
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\CustomFilter;
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

### Optional Logging

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('twenty-crm');
$logger->pushHandler(new StreamHandler('twenty.log', Logger::INFO));

// Pass logger to HTTP client if supported
```

## API Reference

### Client Methods

```php
$client = new TwentyCrmClient($httpClient);

// Get generic entity service for any entity
$service = $client->entity('person');      // GenericEntityService
$service = $client->entity('company');     // GenericEntityService
$service = $client->entity('campaign');    // Works with custom entities!

// Access entity registry (metadata)
$registry = $client->registry();           // EntityRegistry
$definition = $registry->getDefinition('person');
$allEntities = $registry->getAllEntityNames();

// Access metadata service
$metadata = $client->metadata();           // MetadataService
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
use Factorial\TwentyCrm\DTO\FilterBuilder;

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
    ->endsWith('email', '.com')           // suffix
    ->isNull('deletedAt')                 // is NULL
    ->isNotNull('email')                  // isNot NULL
    ->build();
```

#### CustomFilter (Advanced)

For direct filter string control:

```php
use Factorial\TwentyCrm\DTO\CustomFilter;

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

## Migration from v0.x

**v1.0 introduces breaking changes.** The hardcoded `Contact` and `Company` classes have been removed in favor of the dynamic entity system.

**See [MIGRATION.md](MIGRATION.md) for a comprehensive migration guide.**

### Quick Migration Summary

**Before (v0.x):**
```php
use Factorial\TwentyCrm\DTO\Contact;
$contacts = $client->contacts()->find($filter);
```

**After (v1.0 with code generation):**
```php
use MyApp\TwentyCrm\Entities\PersonService;
$personService = new PersonService($client->getHttpClient(), $definition);
$persons = $personService->find($filter);
```

**After (v1.0 with dynamic entities):**
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

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Questions or Issues?**
- Report bugs: [GitHub Issues](https://github.com/factorial-io/twenty-crm-php-client/issues)
- Documentation: [README.md](README.md) | [MIGRATION.md](MIGRATION.md)
- Examples: See `examples/` directory
