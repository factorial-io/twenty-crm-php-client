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

### 2. Factorial Entities (`/factorial-entities/`)

Example implementation with generated entities for Factorial's Twenty CRM instance:

- **Location:** `factorial-entities/` subdirectory
- **Package:** `factorial-io/twenty-crm-entities` (will be extracted to separate repo)
- **Purpose:** Schema-specific entities and integration tests
- **Contains:**
  - Generated Person, Company, Campaign entities
  - Integration tests
  - Test helpers and factories
  - Example usage patterns
- **Tests:** Integration tests (`factorial-entities/tests/Integration/`)

## Separation of Concerns

| Concern | Core Library | Factorial Entities |
|---------|--------------|-------------------|
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

### Factorial Entities (Integration Tests)

```bash
# Setup
cd factorial-entities
cp .env.example .env
# Edit .env with your credentials

# Run integration tests
cd factorial-entities
../vendor/bin/phpunit
```

## Development Workflow

### Working on Core Library

1. Make changes in `src/`
2. Add unit tests in `tests/Unit/`
3. Run `vendor/bin/phpunit`
4. Ensure no entity-specific code

### Working on Factorial Entities

1. Generate entities: `vendor/bin/twenty-generate --config=factorial-entities/.twenty-codegen.php`
2. Add integration tests in `factorial-entities/tests/Integration/`
3. Run `cd factorial-entities && ../vendor/bin/phpunit`
4. Commit generated code

## Future: Separate Repository

The `factorial-entities/` directory is designed to be extracted into a separate repository:

```
factorial-io/twenty-crm-php-client    # Core library (this repo)
factorial-io/twenty-crm-entities      # Generated entities (future repo)
```

When extracted:
- `factorial-entities/` becomes root of new repo
- Add `factorial-io/twenty-crm-php-client` as dependency
- Integration tests move with entities
- Core library remains schema-agnostic

## Why This Structure?

1. **Flexibility:** Users can use core library with any Twenty instance
2. **Type Safety:** Factorial gets typed entities with IDE support
3. **Clean Separation:** No entity-specific code in core library
4. **Easy Migration:** Simple to extract entities to separate repo
5. **Example Code:** Other users see how to generate their own entities

## Code Generation

Generate entities for Factorial's instance:

```bash
# Using config file
vendor/bin/twenty-generate --config=factorial-entities/.twenty-codegen.php

# Or with CLI args
vendor/bin/twenty-generate \
  --api-url=https://factorial.twenty.com/rest/ \
  --api-token=$TWENTY_TOKEN \
  --namespace="Factorial\\TwentyCrm\\Entities" \
  --output=factorial-entities/src \
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
