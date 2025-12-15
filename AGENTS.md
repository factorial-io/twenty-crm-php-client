# Welcome, AI Agent! 👋

Hello! If you're reading this, you're probably an AI assistant about to help with this codebase. This guide was written by another AI (Claude) to help you get up to speed quickly and work effectively in this repository.

## What You're Working With

This is the **Twenty CRM PHP Client** - a library that lets PHP applications talk to the Twenty CRM REST API. What makes this library special is its **dynamic entity system** with optional **code generation** for type safety. Translation: it can work with *any* Twenty CRM instance, whether it has standard entities or custom ones like "Campaign" or "Unicorn" 🦄.

**The Big Idea:** This library doesn't ship with hardcoded entity classes. Instead, it provides tools so users can generate their own typed entities based on *their specific* Twenty CRM instance. Think of it as a framework for working with Twenty CRM, not a one-size-fits-all ORM.

## Quick Start: Your First 5 Minutes

New to this codebase? Here's what to run first:

```bash
# 1. Make sure dependencies are installed
composer install

# 2. Run the tests to ensure everything works
vendor/bin/phpunit tests/Unit

# 3. Check code quality (this project takes it seriously!)
vendor/bin/phpstan analyse src
vendor/bin/phpcs src

# 4. Explore the generated entities example
cd usage-example
cat README.md
```

**Pro tip:** The `usage-example/` directory is your friend! It shows real, working examples of how the library is used.

## Understanding the Architecture (The Fun Part!)

This codebase uses a clever **three-layer system**. Understanding this will make everything else click:

### Layer 1: Runtime (The Core Magic)

This is the dynamic entity system that works without code generation:

- **`DynamicEntity`** - The star of the show! A generic entity that can represent *any* Twenty CRM entity
- **`EntityRegistry`** - Discovers what entities exist by asking the Twenty CRM API
- **`GenericEntityService`** - Handles CRUD for any entity (create, read, update, delete)
- **`TwentyCrmClient`** - Your main entry point

**Why this is cool:** Someone can do `$client->entity('unicorn')->create($data)` even if "unicorn" is a custom entity we've never heard of!

### Layer 2: Code Generation (The Type Safety Boost)

For developers who want IDE autocomplete and type checking:

- **`EntityGenerator`** - Generates typed PHP classes from Twenty CRM metadata
- **`ServiceGenerator`** - Creates typed service wrappers
- **`CollectionGenerator`** - Makes typed collection classes
- **CLI tool** at `bin/twenty-generate`

**Why this exists:** Dynamic entities are flexible but lack IDE support. Generated code gives you `$person->getEmail()` with full autocomplete!

### Layer 3: Field Handling (The Smart Transform Layer)

Handles complex field transformations automatically:

- **`FieldHandlerRegistry`** - Central hub for field transformations
- Various handlers like `PhonesFieldHandler`, `EmailsFieldHandler`, `AddressFieldHandler`
- Automatically converts between API arrays and nice PHP objects

**The magic:** When you call `$entity->get('phones')`, you get a `PhoneCollection` object, not a raw array!

## The Two-Directory Dance 💃

This repo has an unusual structure that trips people up. Let me explain:

### Root Directory (`/`) - The Core Library
- Schema-agnostic tools and runtime
- **Only unit tests here** (`tests/Unit/`)
- No code specific to Person, Company, etc.

### `usage-example/` Directory - The Living Documentation
- Shows the library in action with real generated entities
- **Integration tests live here** (`usage-example/tests/Integration/`)
- Has generated Person, Company, Campaign classes
- Tests against a real Twenty CRM instance

**Remember:** When working on core features, stay in root. When demonstrating usage or running integration tests, use `usage-example/`.

## Common Commands (You'll Use These A Lot)

### Testing Commands

```bash
# Unit tests - fast, no API needed ⚡
vendor/bin/phpunit tests/Unit

# Run all tests
vendor/bin/phpunit

# Tests with coverage report
vendor/bin/phpunit --coverage-html coverage/

# Integration tests (needs real API credentials)
cd usage-example
../vendor/bin/phpunit tests/Integration
```

### Code Quality Commands

```bash
# PHPStan - we require level 5!
vendor/bin/phpstan analyse src

# Check coding standards (PSR-12)
vendor/bin/phpcs src tests

# Auto-fix coding standards
vendor/bin/phpcbf src tests

# PHP CS Fixer - auto-format everything
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff  # Preview first
```

### Code Generation Commands

```bash
# Generate entities from Twenty CRM
vendor/bin/twenty-generate --config=.twenty-codegen.yml

# Generate with services and collections (the full package)
vendor/bin/twenty-generate --config=.twenty-codegen.yml --with-services --with-collections

# Generate just one entity
vendor/bin/twenty-generate --config=.twenty-codegen.yml --entity=campaign

# Override options on the fly
vendor/bin/twenty-generate --namespace="My\\Namespace" --output=custom/path --overwrite
```

## Important Things You Should Know

### 🎯 Field Filtering Strategy (This is Critical!)

When updating entities, the library filters fields to prevent API errors. Here's what you need to know:

**The Approach:**
1. ✅ **Trust the `isSystem` flag** from Twenty CRM metadata (primary filter)
2. ✅ **Explicitly filter auto-managed timestamps** like `createdAt`, `updatedAt` (even though they're marked `isSystem=false`)
3. ❌ **DON'T filter by field type** - some `RELATION` fields ARE updatable!

**Where:** Look at `src/Metadata/FieldConstants.php`

**Why this matters:** We discovered (the hard way!) that trying to update system-managed fields causes 500 errors. But the API's `isSystem` flag isn't perfect - timestamps are auto-managed but not marked as system fields.

### 🔍 The Filter System (Two Approaches)

**Option 1: FilterBuilder (Recommended for most cases)**
```php
// Type-safe, composable, validates against metadata
$filter = FilterBuilder::create()
    ->equals('status', 'ACTIVE')
    ->greaterThan('age', 18)
    ->contains('email', '@example.com')
    ->build();
```

**Option 2: CustomFilter (When you need more control)**
```php
// Direct string, useful for complex edge cases
$filter = new CustomFilter('name[eq]:"John",age[gt]:18');
```

**Filter Syntax:** Twenty CRM uses `field[operator]:value` format (e.g., `name[eq]:"John"` or `age[gt]:18`)

Check `docs/FILTERS.md` for comprehensive examples!

### 🏗️ Code Generation Details

Generated code has specific characteristics:
- Extends `DynamicEntity` (so it works with existing services)
- Typed getters with proper return types
- Setters **only for updatable fields** (not createdAt, etc.)
- Full PHPDoc for IDE happiness
- PSR-12 compliant (thanks to Nette PHP Generator)

**Per entity, you get:**
- `{Entity}.php` - The typed entity class
- `{Entity}Service.php` - Typed service wrapper (with `--with-services`)
- `{Entity}Collection.php` - Typed collection (with `--with-collections`)

### 🎭 Complex Field Magic

Complex fields automatically transform:

```php
$entity->get('phones')          // Returns PhoneCollection, not array!
$entity->get('name')            // Returns Name object with getFullName()
$entity->get('contactAddress')  // Returns Address object with nice methods
$entity->toArray()              // Converts everything back to API format
```

This is handled by field handlers in `src/FieldHandlers/`. Pretty neat!

## Helpful Tips for Working Here

### 💡 When Adding New Features

1. **Start with unit tests** - They're fast and don't need API credentials
2. **Check existing patterns** - Look at similar code (e.g., other field handlers)
3. **Run PHPStan early** - Level 5 is required, so catch issues early
4. **Update documentation** - Future you (or the next AI) will thank you

### 🧪 Testing Strategy

**Unit Tests (`tests/Unit/`):**
- Fast, no credentials needed
- Mock API responses
- Test logic in isolation
- Your first line of defense

**Integration Tests (`usage-example/tests/Integration/`):**
- Test against real Twenty CRM API
- Require `.env` file with real credentials
- Actually create/modify/delete data (use test workspace!)
- Catch real-world issues that mocks miss

**Setting up integration tests:**
```bash
cd usage-example
cp .env.example .env
# Edit .env with TWENTY_API_BASE_URI and TWENTY_API_TOKEN
composer install
../vendor/bin/phpunit
```

### 🎨 Style Guidelines

This project is serious about code quality:

- **Code Style:** PSR-12 (strictly enforced)
- **Type Safety:** PHPStan level 5 (no compromise)
- **Documentation:** PHPDoc on all public methods
- **Line Length:** 120 chars soft limit, 200 absolute max
- **Testing:** Unit tests required for new features

Don't worry - the tools will help you! Run `vendor/bin/php-cs-fixer fix` to auto-format.

## Common Tasks (Step-by-Step)

### Adding a New Field Handler

1. Create handler in `src/FieldHandlers/` implementing `NestedObjectHandler`
2. Implement three methods: `fromApi()`, `toApi()`, `getPhpType()`
3. Register it in `FieldHandlerRegistry::__construct()`
4. Add unit tests in `tests/Unit/FieldHandlers/`
5. Test by regenerating entities: `vendor/bin/twenty-generate --overwrite`

### Supporting a New Filter Operator

1. Add method to `FilterBuilder` (`src/Query/FilterBuilder.php`)
2. Update `buildFilterString()` to handle it
3. Document it in `docs/FILTERS.md` with examples
4. Add unit tests in `tests/Unit/Query/FilterBuilderTest.php`

### Adding a New Code Generator

1. Create generator class in `src/Generator/`
2. Follow the pattern from `EntityGenerator`, `ServiceGenerator`, or `CollectionGenerator`
3. Use Nette PHP Generator for PSR-12 compliance
4. Add CLI flag in `src/Console/GenerateEntitiesCommand.php`
5. Add unit tests in `tests/Unit/Generator/`

## Development Workflow

### Working on Core Library

```bash
# 1. Make your changes in src/
# 2. Add unit tests in tests/Unit/
# 3. Run tests
vendor/bin/phpunit tests/Unit

# 4. Check quality
vendor/bin/phpstan analyse src
vendor/bin/phpcs src

# 5. Fix formatting if needed
vendor/bin/php-cs-fixer fix
```

**Remember:** Core library = no entity-specific code!

### Working on Usage Examples

```bash
# 1. Generate entities
cd usage-example
../bin/twenty-generate --config=.twenty-codegen.yml --overwrite

# 2. Add integration tests
# Create tests in usage-example/tests/Integration/

# 3. Run tests
../vendor/bin/phpunit

# 4. Commit generated code (yes, commit it - it's documentation!)
```

## Documentation You'll Want

- **`README.md`** - Main docs, usage examples, API reference
- **`DEVELOPMENT.md`** - Explains the two-directory structure
- **`TESTING.md`** - Testing guide, integration test setup
- **`MIGRATION.md`** - v0.3 to v0.4 migration (useful for understanding changes)
- **`CONTRIBUTING.md`** - Contribution guidelines
- **`docs/FILTERS.md`** - Filter system deep dive (really comprehensive!)
- **`docs/PREDEFINED_FIELDS.md`** - Reference for common Person/Company fields
- **`docs/dynamic-entity-system-prd.md`** - Architecture decisions (great for understanding "why")

## Version History Quick Reference

**Current: v0.4**
- ✅ Dynamic entity system fully implemented
- ✅ Code generation working great
- ✅ Complex field handlers (phones, emails, addresses)
- ✅ FilterBuilder for type-safe queries
- ✅ Entity relations support
- ✅ PHPStan level 5 compliant

**v0.3 → v0.4 Breaking Changes:**
- Filters moved from `DTO\` to `Query\` namespace
- Entity classes moved from `DTO\` to `Entity\` namespace
- Filter syntax changed from `field eq "value"` to `field[eq]:"value"`

See `MIGRATION.md` if you need to understand the evolution.

## Dependencies Worth Knowing

**Core:**
- **PHP 8.1+** (uses modern features like typed properties, readonly)
- **`nette/php-generator`** - Generates PSR-12 compliant code
- **`symfony/console`** - CLI tool framework
- **`symfony/yaml`** - Config file parsing
- **Guzzle** - HTTP client (PSR-18 compatible)

**Dev:**
- **PHPUnit 10+** - Testing framework
- **PHPStan** - Static analysis (level 5 required)
- **PHP_CodeSniffer** - PSR-12 enforcement
- **PHP CS Fixer** - Auto-formatting

## Final Tips from One AI to Another 🤖

1. **Read the PRD** - `docs/dynamic-entity-system-prd.md` has detailed architecture decisions and "why we did it this way" explanations
2. **Use the example directory** - When in doubt, look at `usage-example/` for working code
3. **Trust the tests** - If all tests pass, you're probably doing it right
4. **Ask questions** - The user usually knows the context you might be missing
5. **Document your changes** - Update relevant .md files when you add features
6. **Be careful with generated code** - It's excluded from some quality checks (see `phpstan.neon`, `phpcs.xml`)

## Need Help?

If you're stuck:
1. Check the relevant documentation file (see list above)
2. Look for similar code in the codebase
3. Read the tests - they often show intended usage
4. Check `docs/dynamic-entity-system-prd.md` for architecture context
5. Ask the user for clarification

Good luck, and happy coding! 🚀

---

*This guide was written with love by Claude (another AI) to help you be successful. If you find something unclear or missing, please update this file for the next AI!*
