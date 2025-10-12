# Filter System Documentation

This document describes the filter system in the Twenty CRM PHP Client, including the composable `FilterBuilder` class and filter operators.

## Table of Contents

- [Overview](#overview)
- [FilterBuilder](#filterbuilder)
- [Filter Operators](#filter-operators)
- [Examples](#examples)
- [Validation](#validation)
- [Advanced Usage](#advanced-usage)

## Overview

The library provides two ways to create filters for querying entities:

1. **FilterBuilder** (Recommended): Composable, type-safe filter builder with validation
2. **CustomFilter**: Simple string-based filter for advanced use cases

## FilterBuilder

The `FilterBuilder` class provides a fluent interface for building filters with automatic validation.

### Basic Usage

```php
use Factorial\TwentyCrm\DTO\FilterBuilder;

// Simple equals filter
$filter = FilterBuilder::create()
    ->equals('name', 'John')
    ->build();

// Multiple conditions (AND)
$filter = FilterBuilder::create()
    ->equals('firstName', 'John')
    ->equals('lastName', 'Doe')
    ->greaterThan('age', 18)
    ->build();

// Multiple conditions (OR)
$filter = FilterBuilder::create()
    ->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING')
    ->build();
```

### Creating FilterBuilder

There are three ways to create a `FilterBuilder`:

```php
// 1. Basic creation (no validation)
$builder = FilterBuilder::create();

// 2. With entity definition (enables validation)
$definition = $client->registry()->getDefinition('person');
$builder = FilterBuilder::forEntity($definition);

// 3. Using constructor
$builder = new FilterBuilder($definition);
```

### Helper Methods

FilterBuilder provides convenient helper methods for all operators:

```php
$builder = FilterBuilder::create();

// Equality
$builder->equals('status', 'ACTIVE');         // field eq value
$builder->notEquals('status', 'DELETED');     // field neq value

// Comparison
$builder->greaterThan('age', 18);             // field gt value
$builder->greaterThanOrEquals('age', 21);     // field gte value
$builder->lessThan('salary', 100000);         // field lt value
$builder->lessThanOrEquals('salary', 50000);  // field lte value

// Array/List
$builder->in('status', ['ACTIVE', 'PENDING']); // field in [values]

// String matching
$builder->contains('email', '@example.com');   // field contains value
$builder->startsWith('name', 'Jo');            // field startsWith value
$builder->endsWith('email', '.com');           // field endsWith value

// NULL checks
$builder->isNull('deletedAt');                 // field is NULL
$builder->isNotNull('email');                  // field isNot NULL
```

### Generic Where Method

For custom operators or special cases:

```php
$builder->where('fieldName', 'operator', $value);

// Examples
$builder->where('age', 'gt', 18);
$builder->where('status', 'in', ['ACTIVE', 'PENDING']);
$builder->where('deletedAt', 'is', 'NULL');
```

## Filter Operators

Twenty CRM supports the following filter operators:

| Operator | Method | Description | Example |
|----------|--------|-------------|---------|
| `eq` | `equals()` | Equals | `name[eq]:"John"` |
| `neq` | `notEquals()` | Not equals | `status[neq]:"DELETED"` |
| `gt` | `greaterThan()` | Greater than | `age[gt]:18` |
| `gte` | `greaterThanOrEquals()` | Greater than or equals | `age[gte]:21` |
| `lt` | `lessThan()` | Less than | `salary[lt]:100000` |
| `lte` | `lessThanOrEquals()` | Less than or equals | `salary[lte]:50000` |
| `in` | `in()` | Value in array | `status[in]:["ACTIVE","PENDING"]` |
| `contains` | `contains()` | Substring match | `email[contains]:"@example.com"` |
| `startsWith` | `startsWith()` | Prefix match | `name[startsWith]:"Jo"` |
| `endsWith` | `endsWith()` | Suffix match | `email[endsWith]:".com"` |
| `is` | `isNull()` | Is NULL | `deletedAt[is]:NULL` |
| `isNot` | `isNotNull()` | Is not NULL | `email[isNot]:NULL` |

### Logical Operators

Combine multiple conditions with AND or OR:

```php
// AND (default) - conditions separated by comma
$builder = FilterBuilder::create()
    ->equals('status', 'ACTIVE')
    ->greaterThan('age', 18);
// Result: status[eq]:"ACTIVE",age[gt]:18

// OR - conditions wrapped in or(...)
$builder = FilterBuilder::create()
    ->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING');
// Result: or(status[eq]:"ACTIVE",status[eq]:"PENDING")

// Switch between AND/OR
$builder->useAnd();  // Use AND (comma-separated)
$builder->useOr();   // Use OR (wrapped in or(...))
```

## Examples

### Example 1: Simple Person Search

```php
use Factorial\TwentyCrm\DTO\FilterBuilder;

// Find persons named John
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')
    ->build();

$persons = $client->entity('person')->find($filter);
```

### Example 2: Complex Person Search

```php
// Find active persons over 21 with @example.com email
$filter = FilterBuilder::create()
    ->equals('status', 'ACTIVE')
    ->greaterThanOrEquals('age', 21)
    ->contains('emails.primaryEmail', '@example.com')
    ->isNotNull('companyId')
    ->build();

$options = new SearchOptions(limit: 20);
$persons = $client->entity('person')->find($filter, $options);
```

### Example 3: Date Range Query

```php
// Find persons created in 2025
$filter = FilterBuilder::create()
    ->greaterThanOrEquals('createdAt', '2025-01-01')
    ->lessThan('createdAt', '2026-01-01')
    ->build();

$persons = $client->entity('person')->find($filter);
```

### Example 4: Multiple Status Values (OR)

```php
// Find persons with ACTIVE or PENDING status
$filter = FilterBuilder::create()
    ->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING')
    ->build();

// Or using IN operator (more concise)
$filter = FilterBuilder::create()
    ->in('status', ['ACTIVE', 'PENDING'])
    ->build();
```

### Example 5: Null/Empty Checks

```php
// Find persons with email but no company
$filter = FilterBuilder::create()
    ->isNotNull('emails.primaryEmail')
    ->isNull('companyId')
    ->build();

$persons = $client->entity('person')->find($filter);
```

### Example 6: Nested Field Access

```php
// Filter by nested fields using dot notation
$filter = FilterBuilder::create()
    ->equals('name.firstName', 'John')
    ->equals('address.city', 'San Francisco')
    ->equals('company.name', 'Acme Corp')
    ->build();
```

## Validation

When you create a FilterBuilder with an entity definition, it validates filters against the metadata:

### Field Validation

```php
$definition = $client->registry()->getDefinition('person');
$builder = FilterBuilder::forEntity($definition);

// Valid field - OK
$builder->equals('status', 'ACTIVE');

// Invalid field - throws InvalidArgumentException
try {
    $builder->equals('unknownField', 'value');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // "Unknown field: unknownField for entity person"
}
```

### SELECT Field Validation

For SELECT and MULTI_SELECT fields, the builder validates enum values:

```php
// Assuming 'status' is a SELECT field with values: ACTIVE, PENDING, INACTIVE

$builder = FilterBuilder::forEntity($definition);

// Valid value - OK
$builder->equals('status', 'ACTIVE');

// Invalid value - throws InvalidArgumentException
try {
    $builder->equals('status', 'INVALID');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
    // "Invalid value 'INVALID' for SELECT field 'status'. Valid values: ACTIVE, PENDING, INACTIVE"
}
```

### Getting Valid Enum Values

```php
// Get valid values for a SELECT field
$definition = $client->registry()->getDefinition('person');
$statusField = $definition->getField('status');

if ($statusField instanceof SelectField) {
    $validValues = $statusField->getValidValues();
    // ['ACTIVE', 'PENDING', 'INACTIVE']

    $options = $statusField->getOptions();
    // [EnumOption('ACTIVE', 'Active', 'green', 0), ...]
}
```

## Advanced Usage

### Reusing FilterBuilder

```php
$builder = FilterBuilder::create();

// Build first filter
$builder->equals('status', 'ACTIVE');
$filter1 = $builder->build();

// Clear and build second filter
$builder->clear();
$builder->equals('status', 'INACTIVE');
$filter2 = $builder->build();
```

### Inspecting Conditions

```php
$builder = FilterBuilder::create()
    ->equals('name', 'John')
    ->greaterThan('age', 18);

// Get all conditions
$conditions = $builder->getConditions();
// [
//     ['field' => 'name', 'operator' => 'eq', 'value' => 'John'],
//     ['field' => 'age', 'operator' => 'gt', 'value' => 18]
// ]

// Check if filters are set
if ($builder->hasFilters()) {
    $filterString = $builder->buildFilterString();
}
```

### Using with Generated Services

```php
use MyApp\TwentyCrm\Entities\PersonService;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\DTO\SearchOptions;

$personService = new PersonService(
    $client->getHttpClient(),
    $client->registry()->getDefinition('person')
);

// Use FilterBuilder with generated service
$filter = FilterBuilder::create()
    ->contains('emails.primaryEmail', '@example.com')
    ->greaterThan('createdAt', '2025-01-01')
    ->build();

$options = new SearchOptions(limit: 50);
$persons = $personService->find($filter, $options);
```

### Dynamic Filter Construction

```php
function buildPersonFilter(array $criteria): CustomFilter
{
    $builder = FilterBuilder::create();

    if (isset($criteria['name'])) {
        $builder->contains('name.firstName', $criteria['name']);
    }

    if (isset($criteria['status'])) {
        $builder->in('status', (array) $criteria['status']);
    }

    if (isset($criteria['minAge'])) {
        $builder->greaterThanOrEquals('age', $criteria['minAge']);
    }

    if (isset($criteria['hasEmail'])) {
        if ($criteria['hasEmail']) {
            $builder->isNotNull('emails.primaryEmail');
        } else {
            $builder->isNull('emails.primaryEmail');
        }
    }

    return $builder->build();
}

// Usage
$filter = buildPersonFilter([
    'name' => 'John',
    'status' => ['ACTIVE', 'PENDING'],
    'minAge' => 21,
    'hasEmail' => true
]);
```

### Escaping Special Characters

FilterBuilder automatically escapes special characters in string values:

```php
// Double quotes are automatically escaped
$builder = FilterBuilder::create()
    ->equals('description', 'This is a "quoted" string');

// Result: description[eq]:"This is a \"quoted\" string"
```

## CustomFilter (Advanced)

For cases where you need direct control over the filter string:

```php
use Factorial\TwentyCrm\DTO\CustomFilter;

// Direct string filter (Twenty CRM format)
$filter = new CustomFilter('name.firstName[eq]:"John",age[gt]:18');

// Or use static factory
$filter = CustomFilter::fromString('status[in]:["ACTIVE","PENDING"]');

// OR conditions
$filter = new CustomFilter('or(status[eq]:"ACTIVE",status[eq]:"PENDING")');

// Use with entity service
$persons = $client->entity('person')->find($filter);
```

**When to use CustomFilter:**
- Complex nested conditions not supported by FilterBuilder
- Integration with existing filter strings
- Performance-critical scenarios (avoids validation overhead)

**When to use FilterBuilder:**
- Most use cases (safer, more maintainable)
- When you need validation
- When building filters programmatically
- When working with SELECT fields

## Best Practices

### 1. Use FilterBuilder for Type Safety

```php
// ✅ Good: Type-safe with validation
$filter = FilterBuilder::forEntity($definition)
    ->equals('status', 'ACTIVE')
    ->build();

// ❌ Avoid: Manual string construction
$filter = new CustomFilter('status[eq]:"ACTIVE"');
```

### 2. Validate SELECT Fields

```php
// ✅ Good: Validation catches invalid values early
$builder = FilterBuilder::forEntity($definition);
try {
    $builder->equals('status', $userInput);
} catch (\InvalidArgumentException $e) {
    // Handle invalid status value
}

// ❌ Avoid: No validation until API call fails
$filter = new CustomFilter("status[eq]:\"{$userInput}\"");
```

### 3. Use Dot Notation for Nested Fields

```php
// ✅ Good: Clear dot notation
$builder->equals('name.firstName', 'John');
$builder->equals('address.city', 'SF');

// ❌ Avoid: Manual path construction
$builder->equals('name["firstName"]', 'John'); // Wrong syntax
```

### 4. Prefer Helper Methods

```php
// ✅ Good: Readable and self-documenting
$builder->greaterThan('age', 18);
$builder->contains('email', '@example.com');

// ❌ Avoid: Generic where() for common operations
$builder->where('age', 'gt', 18);
$builder->where('email', 'contains', '@example.com');
```

### 5. Use IN for Multiple Values

```php
// ✅ Good: Clean and efficient
$builder->in('status', ['ACTIVE', 'PENDING', 'COMPLETED']);

// ❌ Avoid: Multiple OR conditions
$builder->useOr()
    ->equals('status', 'ACTIVE')
    ->equals('status', 'PENDING')
    ->equals('status', 'COMPLETED');
```

## Troubleshooting

### Filter Not Working

**Problem:** Filter returns no results even though data exists.

**Solutions:**
1. Check field names match exactly (case-sensitive)
2. Use dot notation for nested fields: `name.firstName` not `firstName`
3. Verify enum values are valid for SELECT fields
4. Check data types match (string vs number)

```php
// Debug filter string
$filterString = $builder->buildFilterString();
echo "Filter: " . $filterString; // Inspect generated filter
```

### Invalid Field Error

**Problem:** `Unknown field: fieldName for entity person`

**Solutions:**
1. Check field name spelling and case
2. Verify field exists in entity definition:
   ```php
   $definition = $client->registry()->getDefinition('person');
   $fieldNames = $definition->getFieldNames();
   print_r($fieldNames); // See available fields
   ```
3. For nested fields, ensure parent field exists

### Invalid Enum Value Error

**Problem:** `Invalid value 'VALUE' for SELECT field 'status'`

**Solutions:**
1. Get valid values from field metadata:
   ```php
   $field = $definition->getField('status');
   if ($field instanceof SelectField) {
       $validValues = $field->getValidValues();
       print_r($validValues); // See allowed values
   }
   ```
2. Check case sensitivity: 'ACTIVE' vs 'active'
3. Verify Twenty CRM metadata is up to date

## Summary

- **Use `FilterBuilder`** for most filtering needs (type-safe, validated, composable)
- **Use `CustomFilter`** for edge cases requiring manual filter strings
- **Enable validation** by using `FilterBuilder::forEntity($definition)`
- **Use helper methods** (`equals()`, `contains()`, etc.) for readability
- **Use IN operator** for multiple values instead of multiple OR conditions
- **Use dot notation** for nested field access (`name.firstName`)

---

**See Also:**
- [README.md](../README.md) - General usage
- [PREDEFINED_FIELDS.md](PREDEFINED_FIELDS.md) - Field reference
- [MIGRATION.md](../MIGRATION.md) - Migrating from v0.x

**Version:** 1.0
**Last Updated:** 2025-10-12
