# Factorial Twenty CRM Entities

Generated entities for Factorial's Twenty CRM instance. This serves as both:
1. **Production entities** for Factorial's internal use
2. **Reference implementation** for other users of twenty-crm-php-client

## Purpose

This package demonstrates the code generation approach of `twenty-crm-php-client`. Instead of shipping hardcoded entities, the core library provides tools to generate entities tailored to your specific Twenty CRM instance.

## Structure

```
factorial-entities/
├── src/                        # Generated entity classes
│   ├── Person.php             # (to be generated)
│   ├── PersonService.php      # (to be generated)
│   ├── Company.php            # (to be generated)
│   ├── CompanyService.php     # (to be generated)
│   ├── Campaign.php           # (to be generated)
│   └── CampaignService.php    # (to be generated)
├── tests/
│   ├── Integration/           # Integration tests (moved from core library)
│   └── Helpers/              # Test factories
└── .twenty-codegen.php       # Code generation configuration
```

## Usage

### For Factorial Team

```bash
# Generate entities for Factorial's Twenty instance
vendor/bin/twenty-generate --config=.twenty-codegen.php

# Run tests
vendor/bin/phpunit
```

### For Other Users

You have three options:

**Option 1: Use Factorial's entities (if schema matches)**
```json
{
  "require": {
    "factorial-io/twenty-crm-entities": "^1.0"
  }
}
```

**Option 2: Generate your own entities**
```bash
# In your project
vendor/bin/twenty-generate \
  --api-url=https://your-twenty.example.com/rest/ \
  --api-token=$YOUR_TOKEN \
  --namespace="YourApp\\TwentyCrm\\Entities" \
  --output=src/TwentyCrm/Entities \
  --entities=person,company,campaign
```

**Option 3: Use DynamicEntity (no generation needed)**
```php
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\Client\TwentyCrmClient;

$client = new TwentyCrmClient($httpClient);
$person = new DynamicEntity(
    $client->registry()->getDefinition('person'),
    ['emails' => ['primaryEmail' => 'john@example.com']]
);
$created = $client->entity('person')->create($person);
```

## Dependencies

- **factorial-io/twenty-crm-php-client**: Core library providing:
  - DynamicEntity system
  - Code generation CLI
  - Entity relations
  - Metadata discovery
  - HTTP client

## Separation of Concerns

| Core Library | This Package |
|--------------|--------------|
| Tools & runtime | Schema-specific entities |
| DynamicEntity | Generated Person, Company, Campaign |
| Code generator | Generated using code generator |
| Metadata discovery | Integration tests |
| Relations system | Example usage patterns |

## Moving to Separate Repository

This package is designed to be extracted to a separate repository (`factorial-io/twenty-crm-entities`) when ready. All integration tests and entity-specific code lives here, making the core library schema-agnostic.

## Testing

### Running Integration Tests

Integration tests demonstrate working with Twenty CRM entities using the dynamic entity system:

```bash
# Set up environment
cp .env.example .env
# Edit .env with your Twenty API credentials

# Enable integration tests
export TWENTY_TEST_MODE=integration

# Run all integration tests
cd factorial-entities
../vendor/bin/phpunit
```

### Campaign Integration Test

The `CampaignIntegrationTest` demonstrates using the dynamic entity system with custom entities:

```php
// Get campaign service dynamically - no hardcoded DTO needed
$campaignService = $client->entity('campaign');

// Create campaign using DynamicEntity
$campaign = new DynamicEntity(
    $campaignService->getDefinition(),
    [
        'name' => 'Q1 2025 Launch',
        'description' => 'Product launch campaign',
    ]
);

$created = $campaignService->create($campaign);

// Access fields using get() or ArrayAccess
echo $created->get('name');
echo $created['name'];  // Same thing

// Update campaign
$created->set('description', 'Updated description');
$updated = $campaignService->update($created);

// Find campaigns
$campaigns = $campaignService->find($filter, $options);

// Delete campaign
$campaignService->delete($created->getId());
```

**Key Benefits:**
- ✅ Works with any entity (campaign, opportunity, project, etc.)
- ✅ No code generation required
- ✅ Full CRUD operations
- ✅ ArrayAccess, Iterator, JSON serialization built-in
- ✅ Entity metadata available via `getDefinition()`

## Example Code

```php
use Factorial\TwentyCrm\Entities\Person;
use Factorial\TwentyCrm\Entities\PersonService;
use Factorial\TwentyCrm\Entities\Campaign;

// Create person with type safety
$person = new Person($definition);
$person->setEmail('john@example.com');
$person->setFirstName('John');
$person->setLastName('Doe');

$personService = new PersonService($httpClient);
$created = $personService->create($person);

// Work with campaigns (custom entity)
$campaign = new Campaign($definition);
$campaign->setName('Q1 2025 Launch');
$campaign->setStatus('ACTIVE');
// ... IDE autocomplete works!
```

## License

MIT License - See LICENSE file for details
