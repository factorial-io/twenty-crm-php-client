# Predefined Fields Reference

This document provides a reference for the common fields found in Twenty CRM's default entities. These fields are **not hardcoded in the library** but are documented here as a reference for users working with Twenty CRM's default schema.

> **Note:** This is a **reference document only**. The actual fields available in your Twenty CRM instance may differ if you have customizations. Use code generation or the dynamic entity system to work with your specific schema.

## Overview

Twenty CRM's default installation includes two primary entities:

- **Person** (formerly called "Contact" in v0.x of this library)
- **Company** (formerly called "Company" in v0.x)

These entities have standard fields defined by Twenty CRM. This document maps these fields for reference purposes.

## Person Entity

The `person` entity represents individuals in your CRM.

### Standard Fields

| Field Name | Field Type | PHP Type | Description |
|------------|------------|----------|-------------|
| `id` | UUID | `string` | Unique identifier (system-managed) |
| `name` | FULL_NAME | `Name` object | Person's full name (firstName, lastName) |
| `emails` | EMAILS | `string` | Primary email (via field handler) |
| `phones` | PHONES | `PhoneCollection` | Phone numbers collection |
| `jobTitle` | TEXT | `?string` | Job title or position |
| `linkedinLink` | LINKS | `LinkCollection` | LinkedIn profile URL |
| `xLink` | LINKS | `LinkCollection` | X (Twitter) profile URL |
| `city` | TEXT | `?string` | City (part of address) |
| `avatarUrl` | TEXT | `?string` | Avatar image URL |
| `position` | POSITION | `int` | Display position (system-managed) |
| `createdAt` | DATE_TIME | `\DateTimeInterface` | Creation timestamp (auto-managed) |
| `updatedAt` | DATE_TIME | `\DateTimeInterface` | Last update timestamp (auto-managed) |
| `deletedAt` | DATE_TIME | `?\DateTimeInterface` | Soft delete timestamp (auto-managed) |

### Relations

| Relation Name | Relation Type | Target Entity | Description |
|---------------|---------------|---------------|-------------|
| `company` | MANY_TO_ONE | `company` | Person's company |
| `pointOfContactForOpportunities` | ONE_TO_MANY | `opportunity` | Opportunities where person is point of contact |
| `activityTargets` | ONE_TO_MANY | `activityTarget` | Activities targeting this person |
| `favorites` | ONE_TO_MANY | `favorite` | User favorites of this person |
| `attachments` | ONE_TO_MANY | `attachment` | Attachments related to this person |
| `timelineActivities` | ONE_TO_MANY | `timelineActivity` | Timeline activities for this person |

### Complex Field Structures

#### Name (FULL_NAME)

```php
// API format
[
    'firstName' => 'John',
    'lastName' => 'Doe'
]

// PHP object (via field handler)
$name = $person->getName();  // Name object
echo $name->firstName;        // "John"
echo $name->lastName;         // "Doe"
echo $name->getFullName();    // "John Doe"
```

#### Emails (EMAILS)

```php
// API format
[
    'primaryEmail' => 'john@example.com',
    'additionalEmails' => []
]

// PHP (via field handler - simplified)
$email = $person->getEmail();  // "john@example.com" (string)

// Or access full structure
$emails = $person->get('emails');  // array
```

#### Phones (PHONES)

```php
// API format
[
    'primaryPhoneNumber' => '+1234567890',
    'primaryPhoneCountryCode' => 'US',
    'primaryPhoneCallingCode' => '+1',
    'additionalPhones' => []
]

// PHP object (via field handler)
$phones = $person->getPhones();          // PhoneCollection
$primary = $phones->getPrimaryNumber();  // "+1234567890"
$countryCode = $phones->getPrimaryCountryCode();  // "US"
```

#### Links (LINKS)

```php
// API format
[
    'primaryLinkUrl' => 'https://linkedin.com/in/johndoe',
    'primaryLinkLabel' => 'LinkedIn',
    'secondaryLinks' => []
]

// PHP object (via field handler)
$links = $person->getLinkedinLink();    // LinkCollection
$url = $links->getPrimaryUrl();          // "https://linkedin.com/in/johndoe"
```

### Example: Working with Person

```php
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\Name;

$definition = $client->registry()->getDefinition('person');

// Create person
$person = new DynamicEntity($definition, [
    'name' => ['firstName' => 'John', 'lastName' => 'Doe'],
    'emails' => ['primaryEmail' => 'john@example.com'],
    'jobTitle' => 'Software Developer',
    'phones' => [
        'primaryPhoneNumber' => '+1234567890',
        'primaryPhoneCountryCode' => 'US'
    ]
]);

$created = $client->entity('person')->create($person);

// Access fields
$name = $created->get('name');  // ['firstName' => 'John', 'lastName' => 'Doe']
$email = $created->get('emails')['primaryEmail'];  // "john@example.com"
$jobTitle = $created->get('jobTitle');  // "Software Developer"

// With generated code
$person = new Person($definition);
$person->setName(new Name('John', 'Doe'));
$person->setEmail('john@example.com');
$person->setJobTitle('Software Developer');
```

## Company Entity

The `company` entity represents organizations in your CRM.

### Standard Fields

| Field Name | Field Type | PHP Type | Description |
|------------|------------|----------|-------------|
| `id` | UUID | `string` | Unique identifier (system-managed) |
| `name` | TEXT | `string` | Company name |
| `domainName` | DOMAIN | `DomainNameCollection` | Company domain name |
| `address` | ADDRESS | `Address` object | Company address |
| `employees` | NUMBER | `?int` | Number of employees |
| `linkedinLink` | LINKS | `LinkCollection` | LinkedIn company page URL |
| `xLink` | LINKS | `LinkCollection` | X (Twitter) company profile |
| `annualRecurringRevenue` | CURRENCY | `?float` | Annual recurring revenue |
| `idealCustomerProfile` | BOOLEAN | `bool` | Is this an ICP? |
| `position` | POSITION | `int` | Display position (system-managed) |
| `createdAt` | DATE_TIME | `\DateTimeInterface` | Creation timestamp (auto-managed) |
| `updatedAt` | DATE_TIME | `\DateTimeInterface` | Last update timestamp (auto-managed) |
| `deletedAt` | DATE_TIME | `?\DateTimeInterface` | Soft delete timestamp (auto-managed) |

### Relations

| Relation Name | Relation Type | Target Entity | Description |
|---------------|---------------|---------------|-------------|
| `people` | ONE_TO_MANY | `person` | People working at this company |
| `accountOwner` | MANY_TO_ONE | `workspaceMember` | Account owner for this company |
| `opportunities` | ONE_TO_MANY | `opportunity` | Sales opportunities with this company |
| `activityTargets` | ONE_TO_MANY | `activityTarget` | Activities targeting this company |
| `favorites` | ONE_TO_MANY | `favorite` | User favorites of this company |
| `attachments` | ONE_TO_MANY | `attachment` | Attachments related to this company |
| `timelineActivities` | ONE_TO_MANY | `timelineActivity` | Timeline activities for this company |

### Complex Field Structures

#### Address (ADDRESS)

```php
// API format
[
    'addressStreet1' => '123 Main St',
    'addressStreet2' => 'Suite 100',
    'addressCity' => 'San Francisco',
    'addressState' => 'CA',
    'addressPostcode' => '94105',
    'addressCountry' => 'USA',
    'addressLat' => 37.7749,
    'addressLng' => -122.4194
]

// PHP object (via field handler)
$address = $company->getAddress();    // Address object
echo $address->street1;                // "123 Main St"
echo $address->city;                   // "San Francisco"
echo $address->getFormatted();         // "123 Main St, Suite 100, San Francisco, CA 94105, USA"
```

#### Domain Name (DOMAIN)

```php
// API format
[
    'primaryLinkUrl' => 'example.com',
    'primaryLinkLabel' => 'Website',
    'secondaryLinks' => []
]

// PHP object (via field handler)
$domain = $company->getDomainName();  // DomainNameCollection
$primaryDomain = $domain->getPrimaryDomain();  // "example.com"
```

#### Currency (CURRENCY)

```php
// API format
[
    'amountMicros' => 50000000000,  // $50,000 in micros
    'currencyCode' => 'USD'
]

// PHP (simplified via field handler)
$revenue = $company->getAnnualRecurringRevenue();  // 50000.00 (float)
```

### Example: Working with Company

```php
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\Address;

$definition = $client->registry()->getDefinition('company');

// Create company
$company = new DynamicEntity($definition, [
    'name' => 'Acme Corporation',
    'domainName' => ['primaryLinkUrl' => 'acme.com'],
    'employees' => 500,
    'address' => [
        'addressStreet1' => '123 Business Ave',
        'addressCity' => 'New York',
        'addressState' => 'NY',
        'addressPostcode' => '10001',
        'addressCountry' => 'USA'
    ],
    'idealCustomerProfile' => true
]);

$created = $client->entity('company')->create($company);

// Access fields
$name = $created->get('name');  // "Acme Corporation"
$domain = $created->get('domainName')['primaryLinkUrl'];  // "acme.com"
$employees = $created->get('employees');  // 500

// With generated code
$company = new Company($definition);
$company->setName('Acme Corporation');
$company->setEmployees(500);
$company->setIdealCustomerProfile(true);
$company->setAddress(new Address(
    street1: '123 Business Ave',
    city: 'New York',
    state: 'NY',
    postcode: '10001',
    country: 'USA'
));
```

## Field Type Reference

Twenty CRM uses these standard field types:

| Field Type | Description | PHP Type (after transformation) |
|------------|-------------|--------------------------------|
| `TEXT` | Single-line text | `?string` |
| `NUMBER` | Numeric value | `?int` or `?float` |
| `BOOLEAN` | True/false value | `bool` |
| `UUID` | Unique identifier | `string` |
| `DATE_TIME` | Timestamp | `\DateTimeInterface` |
| `DATE` | Date only | `\DateTimeInterface` |
| `SELECT` | Single-choice enum | `?string` (validated) |
| `MULTI_SELECT` | Multiple-choice enum | `array<string>` |
| `RELATION` | Relation to another entity | `DynamicEntity` or `array` |
| `PHONES` | Phone numbers | `PhoneCollection` |
| `EMAILS` | Email addresses | `string` (primary) or array |
| `LINKS` | URLs/links | `LinkCollection` |
| `FULL_NAME` | Name structure | `Name` object |
| `ADDRESS` | Address structure | `Address` object |
| `CURRENCY` | Money amount | `?float` |
| `ACTOR` | User/workspace member | `DynamicEntity` |
| `RATING` | Rating value | `?string` |
| `DOMAIN` | Domain name | `DomainNameCollection` |
| `POSITION` | Display position | `int` |
| `RAW_JSON` | JSON data | `array` |
| `TS_VECTOR` | Full-text search vector | `?string` |

## Custom Fields

Your Twenty CRM instance may have custom fields added by your organization. These will appear in the entity definition alongside standard fields.

### Discovering Custom Fields

```php
// Get all fields for an entity
$definition = $client->registry()->getDefinition('person');
$allFields = $definition->getFields();

foreach ($allFields as $field) {
    echo "{$field->name} ({$field->type->value})";
    if ($field->isCustom) {
        echo " [CUSTOM]";
    }
    echo "\n";
}
```

### Working with Custom Fields

Custom fields work exactly like standard fields:

```php
// Assuming your instance has a custom "department" field on person
$person = new DynamicEntity($definition, [
    'name' => ['firstName' => 'John', 'lastName' => 'Doe'],
    'department' => 'Engineering'  // Custom field
]);

$created = $client->entity('person')->create($person);
echo $created->get('department');  // "Engineering"
```

### Generating Code for Custom Fields

When you generate entities, custom fields are included automatically:

```bash
vendor/bin/twenty-generate --entities=person --with-services
```

Generated `Person.php` will include getters/setters for ALL fields (standard + custom):

```php
// Generated code includes custom fields
$person = new Person($definition);
$person->setDepartment('Engineering');  // Custom field getter/setter
```

## Field Validation

The library validates fields based on entity metadata:

### Enum Validation (SELECT/MULTI_SELECT)

```php
// For SELECT fields, only valid enum values are accepted
$person = new DynamicEntity($definition, [
    'status' => 'ACTIVE'  // Must be a valid enum value
]);

// Get valid values from metadata
$statusField = $definition->getField('status');
if ($statusField instanceof SelectField) {
    $validValues = $statusField->getValidValues();  // ['ACTIVE', 'INACTIVE', 'PENDING']
    $isValid = $statusField->isValidValue('ACTIVE');  // true
}
```

### Required Fields

```php
// Check if field is required
$nameField = $definition->getField('name');
if ($nameField->isRequired()) {
    // This field must be provided
}

// Get all required fields
$requiredFields = $definition->getRequiredFields();
```

## Best Practices

### 1. Use Code Generation for Type Safety

```bash
# Generate entities for your specific schema
vendor/bin/twenty-generate --config=.twenty-codegen.yaml --with-services
```

This gives you:
- IDE autocomplete
- Type hints
- Compile-time error checking
- Custom fields included automatically

### 2. Use Field Handlers for Complex Types

The library automatically transforms complex fields using field handlers:

```php
// Phones are automatically transformed to PhoneCollection
$phones = $person->getPhones();  // PhoneCollection (not array)
$primary = $phones->getPrimaryNumber();

// Links are automatically transformed to LinkCollection
$links = $person->getLinkedinLink();  // LinkCollection
$url = $links->getPrimaryUrl();
```

### 3. Access Metadata for Dynamic Validation

```php
// Get field metadata
$field = $definition->getField('status');

// Check field properties
if ($field->type === FieldType::SELECT) {
    $options = $field->getOptions();  // Available enum options
}

if ($field->isSystem) {
    // Don't try to update this field
}

if ($field->isNullable) {
    // This field can be null
}
```

### 4. Use Relations for Connected Data

```php
// Lazy load related entities
$person = $client->entity('person')->getById($id);
$company = $person->loadRelation('company');

// Eager load for better performance
$options = new SearchOptions(limit: 20, with: ['company']);
$persons = $client->entity('person')->find($filter, $options);

foreach ($persons as $person) {
    $company = $person->getRelation('company');  // Already loaded
}
```

## Mapping from v0.x Contact/Company

For users migrating from v0.x, here's how fields map:

### Contact → Person

| v0.x Contact | v1.0 Person | Notes |
|--------------|-------------|-------|
| `getEmail()` | `getEmail()` | Same (via field handler) |
| `getFirstName()` | `getName()->firstName` | Part of name object |
| `getLastName()` | `getName()->lastName` | Part of name object |
| `getPhones()` | `getPhones()` | Same (PhoneCollection) |
| `getCompanyId()` | `loadRelation('company')->getId()` | Now a relation |
| `getPosition()` | `getJobTitle()` | Field renamed |
| `getLinkedIn()` | `getLinkedinLink()` | Now LinkCollection |
| `getCity()` | `getContactAddress()->city` | Part of address object |

### Company → Company

| v0.x Company | v1.0 Company | Notes |
|--------------|--------------|-------|
| `getName()` | `getName()` | Same |
| `getDomain()` | `getDomainName()` | Now DomainNameCollection |
| `getEmployees()` | `getEmployees()` | Same |
| `getAddress()` | `getAddress()` | Now Address object |
| `getAnnualRevenue()` | `getAnnualRecurringRevenue()` | Field renamed |

See [MIGRATION.md](../MIGRATION.md) for complete migration guide.

---

**Note:** This document is a **reference only**. Your actual Twenty CRM instance schema may differ. Always use the entity registry or code generation to work with your specific schema.

**Version:** 1.0
**Last Updated:** 2025-10-12
