# Migration Guide: v0.x → v1.0

This guide helps you migrate from Twenty CRM PHP Client v0.x (hardcoded entities) to v1.0 (dynamic entity system with code generation).

## Overview of Changes

### What Changed in v1.0

**v1.0 introduces a fundamental architectural shift:**

- **Removed**: Hardcoded `Contact`, `Company` DTOs and their services
- **Added**: Dynamic entity system that works with ANY Twenty CRM entity
- **Added**: Code generation tool (`bin/twenty-generate`)
- **Philosophy Change**: Library provides tools, users generate entities for their specific Twenty CRM instance

### Why This Change?

1. **Every Twenty instance is different** - Custom fields, custom entities, custom schemas
2. **One size fits none** - Hardcoded entities couldn't adapt to custom Twenty configurations
3. **Maintenance burden** - Library maintainers shouldn't maintain schema definitions
4. **Better flexibility** - Users can generate entities matching their exact schema

## Breaking Changes

### Removed APIs

The following classes and methods have been **removed**:

#### Removed Classes

```php
// ❌ REMOVED in v1.0
use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\ContactCollection;
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\Services\ContactService;
use Factorial\TwentyCrm\Services\ContactServiceInterface;

use Factorial\TwentyCrm\DTO\Company;
use Factorial\TwentyCrm\DTO\CompanyCollection;
use Factorial\TwentyCrm\DTO\CompanySearchFilter;
use Factorial\TwentyCrm\Services\CompanyService;
use Factorial\TwentyCrm\Services\CompanyServiceInterface;
```

#### Removed Client Methods

```php
// ❌ REMOVED in v1.0
$client->contacts();  // ContactService
$client->companies(); // CompanyService
```

### Kept Classes

These helper classes are **still available** in v1.0:

```php
// ✅ KEPT in v1.0 (used by field handlers)
use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\DTO\Link;
use Factorial\TwentyCrm\DTO\LinkCollection;
use Factorial\TwentyCrm\DTO\DomainName;
use Factorial\TwentyCrm\DTO\DomainNameCollection;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\DTO\CustomFilter;
```

## Migration Paths

You have **two options** for migrating to v1.0:

### Option 1: Use Code Generation (Recommended)

Generate typed entities for your specific Twenty CRM instance.

**Advantages:**
- ✅ Full IDE autocomplete support
- ✅ Type safety with PHPStan/Psalm
- ✅ Familiar API similar to v0.x
- ✅ Commit generated code to your repository
- ✅ Works with custom entities and custom fields

**Steps:**

1. **Install v1.0**:
   ```bash
   composer require factorial-io/twenty-crm-php-client:^1.0
   ```

2. **Create configuration file** (`.twenty-codegen.yaml`):
   ```yaml
   namespace: MyApp\TwentyCrm\Entities
   output_dir: src/TwentyCrm/Entities
   api_url: https://your-twenty.example.com/rest/
   api_token: ${TWENTY_API_TOKEN}
   entities:
     - person    # Was "contact" in v0.x
     - company
   options:
     overwrite: true
   ```

3. **Generate entities**:
   ```bash
   vendor/bin/twenty-generate --config=.twenty-codegen.yaml --with-services --with-collections
   ```

4. **Update your code**:

   **Before (v0.x):**
   ```php
   use Factorial\TwentyCrm\DTO\Contact;
   use Factorial\TwentyCrm\DTO\ContactSearchFilter;

   $filter = new ContactSearchFilter(email: 'john@example.com');
   $contacts = $client->contacts()->find($filter);

   foreach ($contacts as $contact) {
       echo $contact->getEmail();
   }
   ```

   **After (v1.0 with generated code):**
   ```php
   use MyApp\TwentyCrm\Entities\Person;
   use MyApp\TwentyCrm\Entities\PersonService;
   use Factorial\TwentyCrm\DTO\CustomFilter;
   use Factorial\TwentyCrm\DTO\SearchOptions;

   $personService = new PersonService(
       $client->getHttpClient(),
       $client->registry()->getDefinition('person')
   );

   $filter = new CustomFilter('emails.primaryEmail eq "john@example.com"');
   $persons = $personService->find($filter);

   foreach ($persons as $person) {
       echo $person->getEmail();
   }
   ```

5. **Commit generated code**:
   ```bash
   git add src/TwentyCrm/Entities/
   git commit -m "Add generated Twenty CRM entities for v1.0"
   ```

### Option 2: Use DynamicEntity (Flexible)

Use the dynamic entity system without code generation.

**Advantages:**
- ✅ No code generation needed
- ✅ Works with any entity immediately
- ✅ Flexible for rapid prototyping
- ✅ Adapts automatically to schema changes

**Disadvantages:**
- ⚠️ No IDE autocomplete
- ⚠️ No compile-time type checking
- ⚠️ Field names as strings

**Example:**

**Before (v0.x):**
```php
use Factorial\TwentyCrm\DTO\Contact;

$contact = new Contact();
$contact->setEmail('john@example.com');
$contact->setFirstName('John');
$contact->setLastName('Doe');
$created = $client->contacts()->create($contact);
```

**After (v1.0 with DynamicEntity):**
```php
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\Name;

$definition = $client->registry()->getDefinition('person');
$person = new DynamicEntity($definition, [
    'emails' => ['primaryEmail' => 'john@example.com'],
    'name' => new Name('John', 'Doe')
]);

$created = $client->entity('person')->create($person);
```

## Field Mapping: Contact → Person

Twenty CRM's default entity is **"person"**, not "contact". Here's how fields map:

| v0.x (Contact) | v1.0 (Person) | Type | Notes |
|----------------|---------------|------|-------|
| `getEmail()` | `getEmail()` | `string` | Primary email extracted from `emails` object |
| `getFirstName()` | `getName()->firstName` | `string` | Part of `name` object |
| `getLastName()` | `getName()->lastName` | `string` | Part of `name` object |
| `getPhones()` | `getPhones()` | `PhoneCollection` | Same collection type |
| `getCompany()` | `getCompany()` | Relation | Load with `loadRelation('company')` |
| `getPosition()` | `getJobTitle()` | `string` | Field renamed in Twenty CRM |
| `getLinkedIn()` | `getLinks()` | `LinkCollection` | URL extracted from links |
| `getCity()` | `getContactAddress()->city` | `string` | Part of `address` object |

### Complex Field Changes

#### Emails (Simplified)

**v0.x:**
```php
$email = $contact->getEmail(); // Direct string
```

**v1.0:**
```php
// Option 1: Field handler extracts primary email
$email = $person->getEmail(); // string

// Option 2: Access full emails object
$emails = $person->get('emails'); // ['primaryEmail' => 'john@example.com', ...]
```

#### Name (Structured)

**v0.x:**
```php
$firstName = $contact->getFirstName();
$lastName = $contact->getLastName();
```

**v1.0:**
```php
use Factorial\TwentyCrm\DTO\Name;

// Option 1: Generated getters
$firstName = $person->getFirstName();  // string
$lastName = $person->getLastName();    // string
$fullName = $person->getFullName();    // string (helper method)

// Option 2: Name object
$name = $person->getName(); // Name object
$firstName = $name->firstName;
$lastName = $name->lastName;
$fullName = $name->getFullName();
```

#### Phones (Collection)

**v0.x:**
```php
$phones = $contact->getPhones(); // PhoneCollection
$primary = $phones->getPrimaryNumber();
```

**v1.0:**
```php
$phones = $person->getPhones(); // PhoneCollection (same!)
$primary = $phones->getPrimaryNumber();
```

## Entity Relations

Relations work differently in v1.0:

**v0.x (hardcoded):**
```php
// Relations were not explicitly supported
$companyId = $contact->getCompanyId();
```

**v1.0 (with RelationLoader):**
```php
// Lazy loading
$company = $person->loadRelation('company');
echo $company->get('name');

// Eager loading
$options = new SearchOptions(limit: 10, with: ['company']);
$persons = $personService->find($filter, $options);

foreach ($persons as $person) {
    $company = $person->getRelation('company'); // Already loaded
}
```

## Search and Filtering

### ContactSearchFilter → CustomFilter

**v0.x:**
```php
use Factorial\TwentyCrm\DTO\ContactSearchFilter;

$filter = new ContactSearchFilter(
    email: 'john@example.com',
    name: 'John'
);
```

**v1.0:**
```php
use Factorial\TwentyCrm\DTO\CustomFilter;

// Filter syntax follows Twenty CRM API
$filter = new CustomFilter('emails.primaryEmail eq "john@example.com" and name.firstName eq "John"');

// Or use array syntax
$filter = new CustomFilter(null, [
    'emails.primaryEmail' => ['eq' => 'john@example.com'],
    'name.firstName' => ['eq' => 'John']
]);
```

### SearchOptions

SearchOptions remain the same:

```php
use Factorial\TwentyCrm\DTO\SearchOptions;

$options = new SearchOptions(
    limit: 20,
    orderBy: 'createdAt',
    orderDirection: 'DESC',
    with: ['company'] // NEW: Eager load relations
);
```

## Common Migration Patterns

### Pattern 1: Finding Contacts/Persons

**Before (v0.x):**
```php
$filter = new ContactSearchFilter(email: 'user@example.com');
$contacts = $client->contacts()->find($filter);
```

**After (v1.0 - Generated):**
```php
$filter = new CustomFilter('emails.primaryEmail eq "user@example.com"');
$options = new SearchOptions(limit: 50);
$persons = $personService->find($filter, $options);
```

**After (v1.0 - Dynamic):**
```php
$filter = new CustomFilter('emails.primaryEmail eq "user@example.com"');
$persons = $client->entity('person')->find($filter);
```

### Pattern 2: Creating Contacts/Persons

**Before (v0.x):**
```php
$contact = new Contact();
$contact->setEmail('new@example.com');
$contact->setFirstName('Jane');
$contact->setLastName('Smith');
$created = $client->contacts()->create($contact);
```

**After (v1.0 - Generated):**
```php
use MyApp\TwentyCrm\Entities\Person;
use Factorial\TwentyCrm\DTO\Name;

$person = new Person($definition);
$person->setEmail('new@example.com');
$person->setName(new Name('Jane', 'Smith'));
$created = $personService->create($person);
```

**After (v1.0 - Dynamic):**
```php
$person = new DynamicEntity($definition, [
    'emails' => ['primaryEmail' => 'new@example.com'],
    'name' => ['firstName' => 'Jane', 'lastName' => 'Smith']
]);
$created = $client->entity('person')->create($person);
```

### Pattern 3: Updating Contacts/Persons

**Before (v0.x):**
```php
$contact = $client->contacts()->getById($id);
$contact->setEmail('updated@example.com');
$client->contacts()->update($contact);
```

**After (v1.0):**
```php
$person = $personService->getById($id);
$person->setEmail('updated@example.com');
$personService->update($person);
```

### Pattern 4: Batch Operations

**Before (v0.x):**
```php
$contacts = [$contact1, $contact2, $contact3];
$client->contacts()->batchUpsert($contacts);
```

**After (v1.0):**
```php
$persons = [$person1, $person2, $person3];
$personService->batchUpsert($persons);
```

## FAQ

### Q: Why was Contact renamed to Person?

**A:** Twenty CRM's default entity is "person", not "contact". The v0.x library used "contact" for familiarity, but v1.0 follows Twenty CRM's actual schema.

### Q: Can I still use the old Contact class?

**A:** No. Contact, Company, and their services have been removed in v1.0. This is a breaking change requiring migration.

### Q: Do I have to use code generation?

**A:** No. You can use `DynamicEntity` for a flexible, runtime approach. Code generation is recommended for better IDE support and type safety.

### Q: Will generated code work after I customize my Twenty schema?

**A:** Yes! Re-run `vendor/bin/twenty-generate` whenever your Twenty schema changes. The generator always reflects your current schema.

### Q: How do I work with custom entities (like Campaign)?

**v0.x:** Not possible without library code changes.

**v1.0:** Works immediately:

```php
// With code generation
vendor/bin/twenty-generate --entities=campaign

// Or use DynamicEntity
$campaign = new DynamicEntity($client->registry()->getDefinition('campaign'), [
    'name' => 'Q1 2025 Launch',
    'status' => 'ACTIVE'
]);
$client->entity('campaign')->create($campaign);
```

### Q: What if my Twenty instance has custom fields on Person/Company?

**v0.x:** Custom fields were accessible but not type-safe.

**v1.0:** Generated code includes ALL fields (standard + custom) with proper types.

```bash
# Generate entities matching YOUR exact schema
vendor/bin/twenty-generate --entities=person,company
```

### Q: How do I handle errors after migration?

Error handling remains the same:

```php
use Factorial\TwentyCrm\Exception\TwentyCrmException;
use Factorial\TwentyCrm\Exception\ApiException;

try {
    $person = $personService->getById($id);
} catch (ApiException $e) {
    // Same exception hierarchy as v0.x
    error_log('API error: ' . $e->getMessage());
}
```

### Q: Is there a performance difference?

No significant performance difference. The dynamic entity system uses the same HTTP client and request patterns as v0.x.

Code generation may be slightly faster due to static property access vs array lookups, but the difference is negligible in real-world usage.

## Step-by-Step Migration Checklist

- [ ] 1. **Backup your code** before upgrading
- [ ] 2. **Review breaking changes** in this guide
- [ ] 3. **Choose migration path**: Code generation or DynamicEntity
- [ ] 4. **Update composer.json**: `"factorial-io/twenty-crm-php-client": "^1.0"`
- [ ] 5. **Run composer update**: `composer update factorial-io/twenty-crm-php-client`
- [ ] 6. **If using code generation**:
  - [ ] Create `.twenty-codegen.yaml` config
  - [ ] Run `vendor/bin/twenty-generate --with-services --with-collections`
  - [ ] Commit generated code
- [ ] 7. **Update imports**:
  - [ ] Replace `Contact` with `Person` (or generated class)
  - [ ] Replace `ContactService` with `PersonService` (or generated class)
  - [ ] Replace `ContactSearchFilter` with `CustomFilter`
  - [ ] Replace `Company` with generated `Company` class
  - [ ] Replace `CompanyService` with generated `CompanyService`
- [ ] 8. **Update client calls**:
  - [ ] Replace `$client->contacts()` with `$personService` or `$client->entity('person')`
  - [ ] Replace `$client->companies()` with `$companyService` or `$client->entity('company')`
- [ ] 9. **Update field access**:
  - [ ] `Contact` → `Person` entity name
  - [ ] Check complex fields (emails, name, address) for new structure
  - [ ] Update relation loading to use `loadRelation()` method
- [ ] 10. **Run tests**: Verify all functionality works
- [ ] 11. **Update documentation**: Document new entity classes in your project

## Getting Help

- **Documentation**: See updated [README.md](README.md)
- **Code Generation Guide**: See [docs/CODEGEN.md](docs/CODEGEN.md) (if available)
- **GitHub Issues**: [Report migration issues](https://github.com/factorial-io/twenty-crm-php-client/issues)

## Example: Full Migration

Here's a complete before/after example:

### Before (v0.x)

```php
<?php

use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\ContactSearchFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

$client = new TwentyCrmClient($httpClient);

// Search contacts
$filter = new ContactSearchFilter(email: 'john@example.com');
$options = new SearchOptions(limit: 10);
$contacts = $client->contacts()->find($filter, $options);

// Create contact
$contact = new Contact();
$contact->setEmail('new@example.com');
$contact->setFirstName('Jane');
$contact->setLastName('Doe');
$created = $client->contacts()->create($contact);

// Update contact
$contact = $client->contacts()->getById($id);
$contact->setEmail('updated@example.com');
$client->contacts()->update($contact);
```

### After (v1.0 with Generated Code)

```php
<?php

use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use MyApp\TwentyCrm\Entities\Person;
use MyApp\TwentyCrm\Entities\PersonService;
use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\DTO\Name;

$client = new TwentyCrmClient($httpClient);

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
$person->setEmail('new@example.com');
$person->setName(new Name('Jane', 'Doe'));
$created = $personService->create($person);

// Update person
$person = $personService->getById($id);
$person->setEmail('updated@example.com');
$personService->update($person);
```

### After (v1.0 with DynamicEntity)

```php
<?php

use Factorial\TwentyCrm\Client\TwentyCrmClient;
use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;

$client = new TwentyCrmClient($httpClient);

// Search persons
$filter = new CustomFilter('emails.primaryEmail eq "john@example.com"');
$options = new SearchOptions(limit: 10);
$persons = $client->entity('person')->find($filter, $options);

// Create person
$definition = $client->registry()->getDefinition('person');
$person = new DynamicEntity($definition, [
    'emails' => ['primaryEmail' => 'new@example.com'],
    'name' => ['firstName' => 'Jane', 'lastName' => 'Doe']
]);
$created = $client->entity('person')->create($person);

// Update person
$person = $client->entity('person')->getById($id);
$person->set('emails', ['primaryEmail' => 'updated@example.com']);
$client->entity('person')->update($person);
```

---

**Version:** 1.0
**Last Updated:** 2025-10-12
**Target Audience:** Users migrating from v0.x to v1.0
