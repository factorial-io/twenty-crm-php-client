# Development Guide

## Repository Structure

This repository contains two distinct packages with clear separation of concerns:

### 1. Core Library (`/`)

The main library providing tools and runtime support for Twenty CRM integration:

- **Location:** Root directory
- **Package:** `factorial-io/twenty-crm-php-client`
- **Purpose:** Generic, schema-agnostic tools
- **Contains:**
  - DynamicEntity system
  - Code generation CLI (`bin/twenty-generate`)
  - Metadata discovery
  - Entity relations
  - HTTP client infrastructure
- **Tests:** Unit tests only (`tests/Unit/`)

### 2. Usage Example (`/usage-example/`)

Example implementation with generated entities demonstrating the library:

- **Location:** `usage-example/` subdirectory
- **Package:** `factorial-io/twenty-crm-entities` (example, not published)
- **Purpose:** Usage examples, schema-specific entities, and integration tests
- **Contains:**
  - Generated Person, Company, Campaign entities
  - Integration tests demonstrating library features
  - Test helpers and factories
  - Example usage patterns
- **Tests:** Integration tests (`usage-example/tests/Integration/`)

## Separation of Concerns

| Concern | Core Library | Usage Example |
|---------|--------------|---------------|
| Generic tools | ✅ | ❌ |
| DynamicEntity | ✅ | Uses |
| Code generator | ✅ | Uses |
| Generated entities | ❌ | ✅ |
| Integration tests | ❌ | ✅ |
| Schema-specific code | ❌ | ✅ |
| Unit tests | ✅ | ❌ |

## Running Tests

### Core Library (Unit Tests)

```bash
# Run all unit tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Usage Example (Integration Tests)

```bash
# Setup
cd usage-example
cp .env.example .env
# Edit .env with your credentials

# Run integration tests
cd usage-example
../vendor/bin/phpunit
```

## Development Workflow

### Working on Core Library

1. Make changes in `src/`
2. Add unit tests in `tests/Unit/`
3. Run `vendor/bin/phpunit`
4. Ensure no entity-specific code

### Working on Usage Example

1. Generate entities: `vendor/bin/twenty-generate --config=usage-example/.twenty-codegen.php`
2. Add integration tests in `usage-example/tests/Integration/`
3. Run `cd usage-example && ../vendor/bin/phpunit`
4. Commit generated code

## Usage Example as Template

The `usage-example/` directory serves multiple purposes:

1. **Documentation:** Shows how to use the library with real code
2. **Testing:** Integration tests validate library features against a live Twenty CRM instance
3. **Code Generation Example:** Demonstrates the code generation workflow
4. **Template:** Can be copied/adapted for your own Twenty CRM instance

## Why This Structure?

1. **Flexibility:** Users can use core library with any Twenty instance
2. **Type Safety:** Generated typed entities with IDE support
3. **Clean Separation:** No instance-specific code in core library
4. **Living Documentation:** Real, tested examples of library usage
5. **Example Code:** Shows how to generate and use entities

## Code Generation

Generate entities using the example configuration:

```bash
# Using config file
vendor/bin/twenty-generate --config=usage-example/.twenty-codegen.php

# Or with CLI args
vendor/bin/twenty-generate \
  --api-url=https://your-instance.twenty.com/rest/ \
  --api-token=$TWENTY_TOKEN \
  --namespace="Factorial\\TwentyCrm\\Entities" \
  --output=usage-example/src \
  --entities=person,company,campaign
```

## Quality Checks

```bash
# PHP CodeSniffer
vendor/bin/phpcs

# PHP CS Fixer
vendor/bin/php-cs-fixer fix --dry-run --diff

# PHPStan
vendor/bin/phpstan analyse src
```
