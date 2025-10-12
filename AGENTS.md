# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP client library for the Twenty CRM REST API. The library follows a **dynamic entity system** with optional **code generation** for type safety, allowing it to work with any Twenty CRM instance regardless of custom entities or fields.

**Key Architecture Principle:** The library provides tools and runtime infrastructure, not hardcoded entities. Users generate typed entities specific to their Twenty CRM instance using `bin/twenty-generate`.

## Common Development Commands

### Testing

```bash
# Run unit tests (no credentials required)
vendor/bin/phpunit tests/Unit

# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Run specific test
vendor/bin/phpunit tests/Unit/DTO/DynamicEntityTest.php

# Integration tests (in usage-example directory)
cd usage-example
../vendor/bin/phpunit tests/Integration
```

### Code Quality

```bash
# PHPStan (level 5 compliance required)
vendor/bin/phpstan analyse src

# PHP CodeSniffer (PSR-12 standard)
vendor/bin/phpcs src tests

# Fix coding standards automatically
vendor/bin/phpcbf src tests

# PHP CS Fixer (auto-formatting)
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff  # Preview changes
```

### Code Generation

```bash
# Generate entities from Twenty CRM metadata
vendor/bin/twenty-generate --config=.twenty-codegen.yml

# Generate with services and collections
vendor/bin/twenty-generate --config=.twenty-codegen.yml --with-services --with-collections

# Generate specific entity
vendor/bin/twenty-generate --config=.twenty-codegen.yml --entity=campaign

# Override config options
vendor/bin/twenty-generate --namespace="Custom\\Namespace" --output=custom/path --overwrite

# Test code generation (usage-example)
cd usage-example
../bin/twenty-generate --config=.twenty-codegen.yml --overwrite
```

## Architecture

### Three-Layer System

1. **Runtime Layer (Core Library)**
   - `DynamicEntity` - Generic entity that works with any Twenty CRM entity
   - `EntityRegistry` - Discovers entities from `/metadata/objects` API
   - `GenericEntityService` - CRUD operations for any entity
   - `TwentyCrmClient` - Main entry point

2. **Code Generation Layer (Optional)**
   - `EntityGenerator` - Generates typed entity classes from metadata
   - `ServiceGenerator` - Generates typed service wrappers
   - `CollectionGenerator` - Generates typed collection classes
   - CLI tool: `bin/twenty-generate`

3. **Field Handling Layer**
   - `FieldHandlerRegistry` - Transforms complex fields (phones, emails, addresses)
   - Handlers: `PhonesFieldHandler`, `EmailsFieldHandler`, `NameFieldHandler`, etc.
   - Automatic transformation in `DynamicEntity.get()` and `toArray()`

### Namespace Organization (v0.4+)

```
Factorial\TwentyCrm\
├── Auth\              # Authentication (BearerTokenAuth)
├── Client\            # Main client (TwentyCrmClient)
├── Collection\        # Collections (DynamicEntityCollection)
├── Console\           # CLI commands (GenerateEntitiesCommand)
├── DTO\               # Data transfer objects (Name, Email, Phone, SearchOptions)
├── Entity\            # Entity classes (DynamicEntity)
├── Enums\             # Enumerations (FieldType, RelationType)
├── Exception\         # Exceptions (TwentyCrmException, ApiException)
├── FieldHandlers\     # Field transformers
├── Generator\         # Code generation
├── Http\              # HTTP client (GuzzleHttpClient)
├── Metadata\          # Metadata (EntityDefinition, FieldMetadata)
├── Query\             # Filters (CustomFilter, FilterBuilder)
├── Registry\          # Entity registry
└── Services\          # Services (GenericEntityService, MetadataService)
```

**Important:** In v0.4, filters moved from `DTO\` to `Query\` namespace. See MIGRATION.md for details.

### Two-Directory Structure

This repository has a unique structure with **two distinct packages**:

1. **Core Library (`/`)** - Root directory
   - Generic, schema-agnostic tools and runtime
   - DynamicEntity system, code generation CLI
   - **Unit tests only** (`tests/Unit/`)
   - No entity-specific code

2. **Usage Example (`/usage-example/`)** - Subdirectory
   - Example implementation with generated entities
   - **Integration tests** (`usage-example/tests/Integration/`)
   - Generated Person, Company, Campaign entities
   - Demonstrates library features against real API

**When working on the core library:** Add unit tests in `tests/Unit/`. Avoid entity-specific code.

**When working on usage examples:** Work in `usage-example/`. Integration tests require `.env` with API credentials.

## Key Files and Their Purpose

- `src/Entity/DynamicEntity.php` - Core entity class, works with any Twenty CRM entity
- `src/Registry/EntityRegistry.php` - Discovers entities from Twenty CRM metadata API
- `src/Services/GenericEntityService.php` - CRUD operations for any entity
- `src/Query/FilterBuilder.php` - Type-safe, composable filter builder (v0.4+)
- `src/Generator/EntityGenerator.php` - Generates typed entity classes
- `src/FieldHandlers/FieldHandlerRegistry.php` - Complex field transformation registry
- `src/Metadata/FieldConstants.php` - Centralized field filtering logic for updates
- `bin/twenty-generate` - CLI tool for code generation

## Important Implementation Details

### Field Filtering Strategy

When updating entities via `GenericEntityService`, the library filters fields to prevent 500 errors:

**Hybrid Filtering Approach:**
1. **Primary:** Use `isSystem` flag from Twenty CRM metadata
2. **Secondary:** Explicit list for auto-managed timestamps (`createdAt`, `updatedAt`, `deletedAt`, `createdBy`)
3. **DO NOT filter by field type** - Relations with `isSystem=false` ARE updatable

**Location:** `src/Metadata/FieldConstants.php` (shared between `GenericEntityService` and `EntityGenerator`)

**Rationale:** The Twenty CRM API provides an authoritative `isSystem` flag, but auto-managed timestamps are not marked as system fields despite being database-managed.

### Filter System (v0.4+)

The library provides two approaches for building filters:

1. **FilterBuilder (Recommended)** - `Factorial\TwentyCrm\Query\FilterBuilder`
   - Type-safe, composable filter builder with validation
   - Methods: `equals()`, `contains()`, `greaterThan()`, `in()`, `isNull()`, etc.
   - Supports AND/OR logic with `useAnd()` and `useOr()`
   - Can validate against entity metadata

2. **CustomFilter (Advanced)** - `Factorial\TwentyCrm\Query\CustomFilter`
   - Direct filter string for advanced use cases
   - Use when FilterBuilder doesn't support your needs

**Filter Syntax:** Twenty CRM uses a specific format: `field[operator]:value`
- Example: `name[eq]:"John"` or `age[gt]:18`
- See `docs/FILTERS.md` for comprehensive documentation

### Code Generation

Generated code characteristics:
- Extends `DynamicEntity` for compatibility
- Type-safe getters with proper return types (uses `FieldHandlerRegistry.getPhpType()`)
- Setters **only for updatable fields** (uses `FieldConstants` to exclude read-only fields)
- Full PHPDoc annotations for IDE support
- PSR-12 compliant via Nette PHP Generator

**Generated files per entity:**
- `{Entity}.php` - Typed entity class
- `{Entity}Service.php` - Typed service wrapper (optional with `--with-services`)
- `{Entity}Collection.php` - Typed collection class (optional with `--with-collections`)

### Complex Field Handling

Complex fields (phones, emails, addresses, names) are automatically transformed:

**In DynamicEntity:**
- `get('phones')` returns `PhoneCollection` (not array)
- `get('name')` returns `Name` object (not array)
- `get('contactAddress')` returns `Address` object (not array)
- `toArray()` converts back to API format

**Field Handlers:**
- `PhonesFieldHandler` - `array` ↔ `PhoneCollection`
- `EmailsFieldHandler` - `array` ↔ `string` (extracts primaryEmail)
- `NameFieldHandler` - `array` ↔ `Name` object
- `AddressFieldHandler` - `array` ↔ `Address` object
- `LinksFieldHandler` - `array` ↔ `LinkCollection`

## Testing

### Test Organization

**Unit Tests (`tests/Unit/`):**
- Fast, no API credentials required
- Mock API responses
- Test library logic in isolation
- Run with: `vendor/bin/phpunit tests/Unit`

**Integration Tests (`usage-example/tests/Integration/`):**
- Test against real Twenty CRM API
- Require credentials in `usage-example/.env`
- Test with real Campaign, Person, Company entities
- Run from: `cd usage-example && ../vendor/bin/phpunit`

### Environment Setup for Integration Tests

```bash
cd usage-example
cp .env.example .env
# Edit .env with TWENTY_API_BASE_URI and TWENTY_API_TOKEN
composer install
../vendor/bin/phpunit
```

**Important:** Integration tests create and delete real data. Use a test workspace if possible.

## Configuration

### Code Generation Config

**YAML format (`.twenty-codegen.yml`):**
```yaml
namespace: MyApp\TwentyCrm
output_dir: src/TwentyCrm
api_url: ${TWENTY_API_BASE_URI}  # Environment variable substitution
api_token: ${TWENTY_API_TOKEN}
entities:
  - person
  - company
  - campaign
options:
  generate_services: true
  generate_collections: true
  overwrite: false
```

**Environment variables:**
- Automatic `.env` file loading
- `${VAR_NAME}` syntax in YAML files
- Required: `TWENTY_API_BASE_URI`, `TWENTY_API_TOKEN`

## Migration from v0.3 to v0.4

**Breaking Changes:**
- Namespace reorganization: Filters moved from `DTO\` to `Query\`
  - `DTO\CustomFilter` → `Query\CustomFilter`
  - `DTO\FilterBuilder` → `Query\FilterBuilder`
- Entity classes moved from `DTO\` to `Entity\`
  - `DTO\DynamicEntity` → `Entity\DynamicEntity`
- Filter syntax updated to match Twenty CRM OpenAPI spec
  - Old: `field eq "value"`
  - New: `field[eq]:"value"`

**See MIGRATION.md for complete migration guide.**

## Common Tasks

### Adding a New Field Handler

1. Create handler class in `src/FieldHandlers/` implementing `NestedObjectHandler`
2. Implement `fromApi()`, `toApi()`, and `getPhpType()` methods
3. Register in `FieldHandlerRegistry::__construct()`
4. Add unit tests in `tests/Unit/FieldHandlers/`
5. Regenerate entities to test: `vendor/bin/twenty-generate --overwrite`

### Supporting a New Filter Operator

1. Add method to `FilterBuilder` in `src/Query/FilterBuilder.php`
2. Update filter string building in `buildFilterString()`
3. Add examples to `docs/FILTERS.md`
4. Add unit tests in `tests/Unit/Query/FilterBuilderTest.php`

### Adding a New Code Generator

1. Create generator class in `src/Generator/`
2. Follow pattern from `EntityGenerator`, `ServiceGenerator`, `CollectionGenerator`
3. Use Nette PHP Generator for PSR-12 compliant code
4. Add CLI flag in `src/Console/GenerateEntitiesCommand.php`
5. Add unit tests in `tests/Unit/Generator/`

## Development Workflow

### Working on Core Library

1. Make changes in `src/`
2. Add unit tests in `tests/Unit/`
3. Run tests: `vendor/bin/phpunit tests/Unit`
4. Run quality checks: `vendor/bin/phpstan analyse src`
5. Ensure no entity-specific code

### Working on Usage Example

1. Generate entities: `cd usage-example && ../bin/twenty-generate --config=.twenty-codegen.yml --overwrite`
2. Add integration tests in `usage-example/tests/Integration/`
3. Run tests: `cd usage-example && ../vendor/bin/phpunit`
4. Commit generated code (it's documentation)

## Documentation

Key documentation files:
- `README.md` - Main documentation, usage examples, API reference
- `DEVELOPMENT.md` - Repository structure, separation of concerns
- `TESTING.md` - Testing guide, integration test setup
- `MIGRATION.md` - v0.3 to v0.4 migration guide
- `CONTRIBUTING.md` - Contribution guidelines
- `docs/FILTERS.md` - Comprehensive filter system documentation
- `docs/PREDEFINED_FIELDS.md` - Reference for common Person/Company fields
- `docs/dynamic-entity-system-prd.md` - Architecture PRD (implementation complete)

## Dependencies

**Core:**
- PHP 8.2+ (uses typed properties, named arguments, readonly)
- `cuyz/valinor` - Object mapping and validation
- `nette/php-generator` - Code generation
- `symfony/console` - CLI tool
- `symfony/yaml` - YAML config parsing
- PSR-18 HTTP client (Guzzle recommended)

**Dev:**
- PHPUnit 10+ for testing
- PHPStan level 5 for static analysis
- PHP_CodeSniffer for PSR-12 compliance
- PHP CS Fixer for auto-formatting

## Current Status (v0.4)

**Implemented Features:**
- ✅ Dynamic entity system (DynamicEntity, EntityRegistry)
- ✅ Entity discovery from `/metadata/objects` API
- ✅ Code generation (entities, services, collections)
- ✅ Complex field handlers (phones, emails, addresses, names)
- ✅ FilterBuilder with type-safe query building
- ✅ Entity relations support (MANY_TO_ONE, ONE_TO_MANY)
- ✅ Field filtering for safe updates
- ✅ PHPStan level 5 compliance
- ✅ Comprehensive documentation

**Branch:** `main` (v0.4 merged)
**Test Status:** All unit and integration tests passing
**Quality:** PHPStan level 5, PSR-12 compliant

## Style and Conventions

- **Code Style:** PSR-12 (enforced by PHPCS and PHP CS Fixer)
- **Type Safety:** Full type hints required, PHPStan level 5
- **Documentation:** PHPDoc required for public methods
- **Line Length:** 120 characters soft limit, 200 absolute
- **Naming:**
  - Classes: PascalCase
  - Methods: camelCase
  - Constants: UPPER_SNAKE_CASE
- **Testing:** Unit tests required for new core functionality
- **Generated Code:** May be excluded from quality checks (see phpstan.neon, phpcs.xml)
