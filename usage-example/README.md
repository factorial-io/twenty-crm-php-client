# Twenty CRM PHP Client - Usage Example

This directory demonstrates how to use the **Twenty CRM PHP Client** library with real code examples and integration tests.

## Purpose

The `usage-example/` folder serves four purposes:

1. **📚 Living Documentation** - Real, working code showing how to use the library
2. **✅ Integration Testing** - Tests validating library features against a live Twenty CRM instance
3. **🔧 Code Generation Example** - Demonstrates the code generation workflow
4. **📋 Template** - Can be copied and adapted for your own Twenty CRM instance

## What's Inside

```
usage-example/
├── src/                        # Generated entity classes
│   ├── Person.php             # Generated Person entity with type-safe methods
│   ├── PersonService.php      # Service class for Person CRUD operations
│   ├── PersonCollection.php   # Typed collection for Person entities
│   ├── Company.php            # Generated Company entity
│   ├── CompanyService.php     # Service class for Company operations
│   ├── CompanyCollection.php  # Typed collection for Company entities
│   ├── Campaign.php           # Generated Campaign entity (custom entity)
│   ├── CampaignService.php    # Service class for Campaign operations
│   └── CampaignCollection.php # Typed collection for Campaign entities
├── tests/
│   ├── Integration/           # Integration tests demonstrating library features
│   │   ├── PersonCompanyRelationTest.php
│   │   ├── FilterBuilderIntegrationTest.php
│   │   └── CampaignIntegrationTest.php
│   ├── IntegrationTestCase.php
│   └── Helpers/               # Test helpers and utilities
├── .twenty-codegen.php        # Code generation configuration
├── phpunit.xml                # PHPUnit configuration for integration tests
└── .env.example              # Environment configuration template
```

## Two Ways to Use Twenty CRM PHP Client

This example demonstrates **both** approaches supported by the library:

### Option 1: Generated Entities (Type-Safe)

Generate typed entity classes with full IDE autocomplete support:

```php
use Factorial\TwentyCrm\Entities\Person;
use Factorial\TwentyCrm\Entities\PersonService;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\DTO\SearchOptions;

// Create person with type safety and IDE autocomplete
$personService = new PersonService($httpClient, $definition);

$person = new Person($definition);
$person->setFirstName('John');
$person->setLastName('Doe');
$person->setEmail('john@example.com');  // IDE suggests all methods!

$created = $personService->create($person);

// Find persons with FilterBuilder
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')
    ->contains('emails.primaryEmail', '@example.com')
    ->build();

$options = new SearchOptions(limit: 20);
$persons = $personService->find($filter, $options);  // Returns PersonCollection

// Type-safe access
foreach ($persons as $person) {
    echo $person->getFirstName() . ' ' . $person->getLastName();
    echo $person->getEmail();
}
```

**Benefits:**
- ✅ Full IDE autocomplete and type hints
- ✅ Compile-time type checking with PHPStan
- ✅ Self-documenting code with typed methods
- ✅ Less runtime errors

### Option 2: DynamicEntity (Flexible)

Use the dynamic entity system without code generation:

```php
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\Client\TwentyCrmClient;

$client = new TwentyCrmClient($httpClient);

// Works with ANY entity (no generation needed)
$personService = $client->entity('person');

// Create person dynamically
$person = new DynamicEntity($personService->getDefinition(), [
    'name' => ['firstName' => 'John', 'lastName' => 'Doe'],
    'emails' => ['primaryEmail' => 'john@example.com'],
]);

$created = $personService->create($person);

// Find persons with FilterBuilder
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')
    ->build();

$persons = $personService->find($filter);

// Access fields using get() or ArrayAccess
foreach ($persons as $person) {
    $name = $person->get('name');
    echo $name->getFirstName() . ' ' . $name->getLastName();
    echo $person['emails']->getPrimaryEmail();  // ArrayAccess also works
}
```

**Benefits:**
- ✅ Works with any entity (including custom entities)
- ✅ No code generation required
- ✅ Rapid prototyping and experimentation
- ✅ Automatically handles complex fields (Name, Emails, Phones, etc.)

## Running the Examples

### Setup

```bash
# 1. Copy environment configuration
cp .env.example .env

# 2. Edit .env with your Twenty CRM credentials
# TWENTY_API_BASE_URI=https://your-instance.twenty.com/rest/
# TWENTY_API_TOKEN=your-api-token-here

# 3. Install dependencies (if not already installed)
cd ..
composer install
```

### Generate Entities

```bash
# From the project root
vendor/bin/twenty-generate --config=usage-example/.twenty-codegen.php

# Or with custom settings
vendor/bin/twenty-generate \
  --api-url=https://your-instance.twenty.com/rest/ \
  --api-token=$TWENTY_TOKEN \
  --namespace="Factorial\\TwentyCrm\\Entities" \
  --output=usage-example/src \
  --entities=person,company,campaign
```

### Run Integration Tests

```bash
# From usage-example directory
cd usage-example
../vendor/bin/phpunit

# Or from project root
vendor/bin/phpunit usage-example/tests/

# Run specific test
vendor/bin/phpunit usage-example/tests/Integration/FilterBuilderIntegrationTest.php
```

## Integration Test Examples

The integration tests demonstrate real-world usage patterns:

### Person-Company Relations

`tests/Integration/PersonCompanyRelationTest.php` shows:
- Creating persons and companies
- Setting up relations between entities
- Accessing related entities
- Bi-directional relations

### FilterBuilder Usage

`tests/Integration/FilterBuilderIntegrationTest.php` demonstrates:
- Simple filters (`equals`, `contains`, `startsWith`)
- Complex filters with multiple conditions
- Nested field access (`name.firstName`, `emails.primaryEmail`)
- Validation with entity metadata
- Special characters in filter values

### Custom Entity (Campaign)

`tests/Integration/CampaignIntegrationTest.php` shows:
- Working with custom entities using DynamicEntity
- Full CRUD operations
- No code generation required
- Works with any Twenty CRM entity

## Using This as a Template

You can copy this directory structure for your own Twenty CRM instance:

```bash
# 1. Copy the usage-example directory
cp -r usage-example my-twenty-entities

# 2. Update namespace in .twenty-codegen.php
# Change "Factorial\\TwentyCrm\\Entities" to "MyApp\\TwentyCrm\\Entities"

# 3. Configure your Twenty CRM instance
cp my-twenty-entities/.env.example my-twenty-entities/.env
# Edit .env with your credentials

# 4. Generate your entities
vendor/bin/twenty-generate --config=my-twenty-entities/.twenty-codegen.php

# 5. Run tests to verify
cd my-twenty-entities
../vendor/bin/phpunit
```

## Key Features Demonstrated

### FilterBuilder (Composable Filters)

Build type-safe filters with validation:

```php
use Factorial\TwentyCrm\DTO\FilterBuilder;

// Get entity definition for validation
$definition = $client->registry()->getDefinition('person');

// Build filter with validation
$filter = FilterBuilder::forEntity($definition)
    ->equals('status', 'ACTIVE')           // Validates enum values
    ->greaterThan('createdAt', '2025-01-01')
    ->contains('emails.primaryEmail', '@example.com')
    ->isNotNull('companyId')
    ->build();

$persons = $client->entity('person')->find($filter);
```

See `docs/FILTERS.md` for complete filter documentation.

### Complex Field Handling

Automatic transformation of complex fields:

```php
// Complex fields are automatically converted to PHP objects
$person = $personService->findOneById($id);

// Name field (composite)
$name = $person->get('name');
echo $name->getFirstName();
echo $name->getLastName();

// Emails field (composite)
$emails = $person->get('emails');
echo $emails->getPrimaryEmail();

// Phones (collection)
$phones = $person->get('phones');
foreach ($phones as $phone) {
    echo $phone->number . ' (' . $phone->type . ')';
}
```

### Entity Relations

Access related entities easily:

```php
// Set company relation
$person->set('company', $company);
$personService->update($person);

// Access related company
$relatedCompany = $person->get('company');
if ($relatedCompany) {
    echo $relatedCompany->get('name');
}
```

## Learn More

- **Main README:** `../README.md` - Library overview and installation
- **Filter Documentation:** `../docs/FILTERS.md` - Complete filter system guide
- **Migration Guide:** `../MIGRATION.md` - Upgrading from v0.x to v1.0
- **Predefined Fields:** `../docs/PREDEFINED_FIELDS.md` - Person and Company field reference
- **Development Guide:** `../DEVELOPMENT.md` - Contributing and development workflow

## Dependencies

This example uses the core library:

```json
{
  "require": {
    "factorial-io/twenty-crm-php-client": "^1.0"
  }
}
```

The core library provides:
- DynamicEntity system
- Code generation CLI (`bin/twenty-generate`)
- FilterBuilder for composable queries
- Metadata discovery and entity registry
- Entity relations support
- Complex field handlers (Name, Emails, Phones, etc.)

## License

MIT License - See LICENSE file for details
