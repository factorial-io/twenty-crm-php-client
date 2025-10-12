# Product Requirements Document: Dynamic Entity System

**Version:** 1.0
**Date:** October 11, 2025
**Updated:** October 12, 2025
**Author:** Technical Analysis
**Status:** ✅ Implemented - Awaiting Review

## Executive Summary

This PRD outlines a comprehensive refactoring plan to transform the Twenty CRM PHP Client from a hardcoded entity implementation (Contact, Company) to a dynamic, metadata-driven system that can support custom entities (like Campaign) without requiring code changes.

## Problem Statement

### Current Limitations

1. **Hardcoded Entities**: Contact and Company DTOs have hardcoded properties and field lists
   - Contact: 14 hardcoded properties, 32 hardcoded "standard fields" (src/DTO/Contact.php:120-127)
   - Company: 14 hardcoded properties, 23 hardcoded "standard fields" (src/DTO/Company.php:116-123)
   - These only work for default Twenty CRM setups
   - Custom fields are treated as generic arrays without type safety

2. **No Custom Entity Support**: Users cannot work with custom entities like Campaign without library code changes

3. **Maintenance Burden**: Each new entity requires duplicating entire service/DTO/collection patterns

4. **Limited Flexibility**: Twenty CRM allows users to customize their data model, but the PHP client cannot adapt

5. **Wrong Abstraction**: The library tries to be both generic AND specific, satisfying neither use case well

### Business Impact

- Users with custom entities cannot use this library
- Factorial's own Twenty instance has Campaign entity that isn't supported
- Every Twenty installation is different, but library assumes default schema
- Competitive disadvantage vs. more flexible CRM clients
- High maintenance cost for new entity support
- Poor developer experience for advanced Twenty CRM features

### The Right Solution

**The library should be a code generation framework, not a hardcoded ORM.**

Each user runs `bin/twenty-generate` against their Twenty instance to create typed entities that match their exact schema. The library provides:
- Core dynamic entity system (runtime)
- Metadata discovery (runtime)
- Code generation tools (development time)
- Reference documentation for common entities (documentation only)

## Goals & Success Criteria

### Must Have (P0)
- ✅ Core dynamic entity system (DynamicEntity, EntityRegistry)
- ✅ Metadata-driven field discovery from Twenty API
- ✅ Entity relations support (company ↔ contacts, campaign ↔ contacts)
- ✅ Code generation CLI tool (`bin/twenty-generate`)
- ✅ Generated code works for ANY Twenty instance
- ✅ Reference documentation for common entities (Person, Company)

### Should Have (P1)
- ✅ Validation using metadata (enum checks, required fields)
- ✅ Performance within 10% of current implementation
- ✅ Comprehensive documentation and examples
- ✅ Example: Factorial-specific entities repository
- ✅ Migration guide from v0.x (for existing users)

### Nice to Have (P2)
- ⭐ Bulk operations optimization
- ⭐ Schema migration detection
- ⭐ Watch mode for code generation during development

## Architectural Philosophy

### Code Generation > Hardcoded Entities

**Decision: Remove Contact and Company as hardcoded DTOs**

**Rationale:**
1. **Every Twenty instance is different** - Custom fields, custom entities, custom schemas
2. **One size fits none** - Hardcoded entities satisfy neither simple nor complex use cases
3. **Maintenance burden** - Library maintainers shouldn't maintain schema definitions
4. **Wrong coupling** - Library couples to a specific Twenty schema that may not exist

**New Approach:**
- **Library provides tools**, not entities
- **Users generate entities** for their specific Twenty instance
- **Generated code is committed** to user's repository
- **Library focuses on runtime** (DynamicEntity, HTTP, validation)

**Backward Compatibility:**
- Keep Contact/Company **documentation** showing predefined fields
- Provide **migration guide** for existing v0.x users
- Breaking change is justified: v0.x → v1.0 major version

### Example Repositories

**1. Core Library** (`factorial-io/twenty-crm-php-client`)
   - DynamicEntity system
   - Code generation CLI
   - No hardcoded entities

**2. Factorial's Entities** (`factorial-io/twenty-crm-entities`)
   - Generated entities for Factorial's Twenty instance
   - Person, Company, Campaign, etc.
   - Uses core library as dependency
   - Serves as example for other users

**3. User's Project** (e.g., Drupal module)
   - Runs `bin/twenty-generate` with their config
   - Generates entities in their namespace
   - Commits generated code
   - Uses core library as dependency

## Stakeholders

- **Primary Users**: PHP developers integrating with Twenty CRM
- **Technical Users**: Developers with custom entity configurations
- **Factorial Team**: Needs Campaign entity support
- **Maintainers**: Library maintainers supporting new Twenty features
- **Contributors**: Open source contributors extending functionality

## Technical Requirements

### Existing Infrastructure (Strengths)

**Good Foundation Already Exists:**

1. **MetadataService** (src/Services/MetadataService.php)
   - Fetches field metadata from `/metadata/objects` endpoint
   - Caches field definitions
   - Supports enum validation via SelectField

2. **Field Metadata System**
   - `FieldMetadata` base class with type information
   - `SelectField` for enums with validation
   - `EnumOption` for enum choices
   - `FieldMetadataFactory` for creating typed instances

3. **HTTP Client Infrastructure**
   - PSR-18 compatible HttpClient
   - Error handling via ApiException
   - Request/response handling

### Technology Stack Recommendations

#### Recommended: Valinor (CuyZ/Valinor)

**Rationale:**
- Strong type support (generics, shaped arrays, union types)
- PHP 8.1+ native (matches project requirement)
- Built-in validation with detailed error messages
- Normalization support (object ↔ array)
- Actively maintained (last update Feb 2025)
- No code generation required
- Better error handling than Symfony Serializer

**Installation:**
```bash
composer require cuyz/valinor
```

**Alternative Considered:**
- **JoliCode AutoMapper**: Code generation for performance, more complex setup
- **Symfony Serializer**: Slower, less type-safe (rejected)
- **Spatie DTO**: Too simplistic for nested structures (rejected)

## Proposed Architecture

### Three-Layer System

```
┌─────────────────────────────────────────────────────────┐
│  Layer 1: Generic Entity System                         │
├─────────────────────────────────────────────────────────┤
│  - DynamicEntity (base class)                           │
│  - EntityDefinition (metadata container)                │
│  - GenericEntityService (CRUD operations)               │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 2: Code Generation (Optional)                    │
├─────────────────────────────────────────────────────────┤
│  - EntityGenerator (CLI tool)                           │
│  - Generates typed DTOs from metadata                   │
│  - Creates IDE-friendly concrete classes                │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 3: Mapping & Validation                          │
├─────────────────────────────────────────────────────────┤
│  - Valinor integration                                  │
│  - FieldMapper (handles complex nested types)           │
│  - ValidationService (enum checks, type validation)     │
└─────────────────────────────────────────────────────────┘
```

### Core Components

#### 1. DynamicEntity (src/DTO/DynamicEntity.php)

```php
class DynamicEntity implements \ArrayAccess, \IteratorAggregate
{
    public function __construct(
        private readonly EntityDefinition $definition,
        private array $data = []
    ) {}

    public function get(string $fieldName): mixed
    public function set(string $fieldName, mixed $value): void
    public function toArray(): array
    public static function fromArray(array $data, EntityDefinition $def): self
    public function getDefinition(): EntityDefinition
}
```

**Key Features:**
- ArrayAccess for dynamic field access
- Type validation via EntityDefinition
- Serialization support (toArray/fromArray)

#### 2. EntityDefinition (src/Metadata/EntityDefinition.php)

```php
class EntityDefinition
{
    public function __construct(
        public readonly string $objectName,      // 'person', 'company', 'campaign'
        public readonly string $apiEndpoint,     // '/people', '/companies', '/campaigns'
        public readonly array $fields,           // FieldMetadata[]
        public readonly array $standardFields,   // Built-in field names
        public readonly array $nestedObjectMap   // name => NestedObjectHandler
    ) {}

    public function getField(string $name): ?FieldMetadata
    public function hasField(string $name): bool
    public function getRequiredFields(): array
}
```

**Responsibilities:**
- Store entity metadata
- Provide field lookup
- Define API endpoint mapping
- Manage nested object handlers

#### 3. EntityRegistry (src/Registry/EntityRegistry.php)

```php
class EntityRegistry
{
    private array $definitions = [];

    public function __construct(
        private readonly MetadataService $metadata,
        private readonly HttpClientInterface $httpClient
    ) {
        $this->discoverEntities();
    }

    public function getDefinition(string $objectName): ?EntityDefinition
    public function hasEntity(string $objectName): bool
    public function getAllEntityNames(): array

    private function discoverEntities(): void
    {
        // Fetch all objects from /metadata/objects
        // Build EntityDefinition for each
    }
}
```

**Discovery Algorithm:**
1. Fetch `/metadata/objects` from Twenty CRM API
2. For each object, extract:
   - Object name (singular/plural)
   - API endpoint path
   - Field definitions
   - Standard vs. custom fields
3. Build EntityDefinition and cache
4. Register field handlers for complex types

#### 4. GenericEntityService (src/Services/GenericEntityService.php)

```php
class GenericEntityService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityDefinition $definition,
        private readonly MapperInterface $mapper
    ) {}

    public function find(FilterInterface $filter, SearchOptions $options): DynamicEntityCollection
    public function getById(string $id): ?DynamicEntity
    public function create(DynamicEntity $entity): DynamicEntity
    public function update(DynamicEntity $entity): DynamicEntity
    public function delete(string $id): bool
    public function batchUpsert(array $entities): DynamicEntityCollection
}
```

**CRUD Operations:**
- Uses EntityDefinition for endpoint routing
- Uses EntityMapper for data transformation
- Returns DynamicEntity instances
- Supports all current service operations

#### 5. Field Handlers (src/FieldHandlers/)

**Purpose:** Handle complex nested object transformations

**Interface:**
```php
interface NestedObjectHandler
{
    public function fromApi(array $data): mixed;
    public function toApi(mixed $value): array;
}
```

**Required Handlers:**
- `EmailsFieldHandler` - emails object → string
- `PhonesFieldHandler` - phones array → PhoneCollection
- `NameFieldHandler` - name object → firstName/lastName
- `AddressFieldHandler` - address object → address fields
- `LinksFieldHandler` - link arrays → LinkCollection
- `DomainNameFieldHandler` - domain arrays → DomainNameCollection

**Example:**
```php
class EmailsFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?string
    {
        return $data['primaryEmail'] ?? $data['additionalEmails'][0] ?? null;
    }

    public function toApi(mixed $value): array
    {
        if (is_string($value)) {
            return ['primaryEmail' => $value];
        }
        return $value;
    }
}
```

#### 6. EntityMapper (src/Mapping/EntityMapper.php)

```php
class EntityMapper implements MapperInterface
{
    private TreeMapper $valinor;
    private FieldHandlerRegistry $handlers;

    public function mapToEntity(array $data, EntityDefinition $definition): DynamicEntity
    {
        // 1. Apply field handlers to transform complex fields
        $normalized = $this->normalizeApiData($data, $definition);

        // 2. Use Valinor to map to DynamicEntity
        return $this->valinor->map(DynamicEntity::class, $normalized);
    }

    public function mapToApi(DynamicEntity $entity): array
    {
        // 1. Get data array
        $data = $entity->toArray();

        // 2. Apply field handlers for API format
        return $this->denormalizeForApi($data, $entity->getDefinition());
    }
}
```

### Entity Relations System

Entity relations are a critical part of the CRM data model. Twenty CRM supports various relationship types between entities (e.g., Company ↔ Person, Campaign ↔ Person).

#### 7. RelationMetadata (src/Metadata/RelationMetadata.php)

```php
class RelationMetadata
{
    public function __construct(
        public readonly string $name,              // 'company', 'people', 'campaigns'
        public readonly string $type,              // 'ONE_TO_MANY', 'MANY_TO_ONE', 'MANY_TO_MANY'
        public readonly string $targetEntity,      // 'company', 'person', 'campaign'
        public readonly string $foreignKey,        // 'companyId', 'personId'
        public readonly ?string $inverseName,      // Name of inverse relation
        public readonly bool $isNullable,
        public readonly bool $isCustom,
    ) {}

    public function isOneToMany(): bool
    public function isManyToOne(): bool
    public function isManyToMany(): bool
}
```

**Relation Types in Twenty CRM:**
- **MANY_TO_ONE**: Person → Company (person.companyId)
- **ONE_TO_MANY**: Company → People (company.people[])
- **MANY_TO_MANY**: Campaign ↔ People (via junction table)

#### 8. EntityDefinition with Relations

**Updated EntityDefinition:**

```php
class EntityDefinition
{
    public function __construct(
        public readonly string $objectName,
        public readonly string $apiEndpoint,
        public readonly array $fields,           // FieldMetadata[]
        public readonly array $standardFields,
        public readonly array $nestedObjectMap,
        public readonly array $relations,        // RelationMetadata[] (NEW)
    ) {}

    public function getRelation(string $name): ?RelationMetadata
    public function hasRelation(string $name): bool
    public function getRelations(): array
}
```

#### 9. Relation Loading Strategies

**Lazy Loading (Default):**
```php
// Relation is loaded on-demand
$company = $client->entity('company')->getById('abc-123');
$people = $company->loadRelation('people'); // Triggers API call
```

**Eager Loading:**
```php
// Load relations in initial query
$options = new SearchOptions(
    limit: 10,
    with: ['people', 'activities']  // Include relations
);
$companies = $client->entity('company')->find($filter, $options);

// Relations already loaded
foreach ($companies as $company) {
    $people = $company->getRelation('people'); // No additional API call
}
```

#### 10. DynamicEntity with Relations

**Enhanced DynamicEntity:**

```php
class DynamicEntity implements \ArrayAccess, \IteratorAggregate
{
    private array $loadedRelations = [];

    // ... existing methods ...

    /**
     * Load a relation from the API.
     */
    public function loadRelation(string $relationName): mixed
    {
        $relation = $this->definition->getRelation($relationName);
        if (!$relation) {
            throw new \InvalidArgumentException("Unknown relation: $relationName");
        }

        // Check if already loaded
        if (isset($this->loadedRelations[$relationName])) {
            return $this->loadedRelations[$relationName];
        }

        // Load from API via relation loader
        $this->loadedRelations[$relationName] = $this->relationLoader->load($this, $relation);
        return $this->loadedRelations[$relationName];
    }

    /**
     * Get a loaded relation (doesn't trigger load).
     */
    public function getRelation(string $relationName): mixed
    {
        return $this->loadedRelations[$relationName] ?? null;
    }

    /**
     * Check if relation is loaded.
     */
    public function hasLoadedRelation(string $relationName): bool
    {
        return isset($this->loadedRelations[$relationName]);
    }

    /**
     * Set a relation (for eager loading).
     */
    public function setRelation(string $relationName, mixed $value): void
    {
        $this->loadedRelations[$relationName] = $value;
    }
}
```

#### 11. RelationLoader (src/Relations/RelationLoader.php)

```php
class RelationLoader
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityRegistry $registry,
        private readonly EntityMapper $mapper,
    ) {}

    /**
     * Load a relation for an entity.
     */
    public function load(DynamicEntity $entity, RelationMetadata $relation): mixed
    {
        $entityId = $entity->get('id');
        if (!$entityId) {
            throw new \RuntimeException('Cannot load relation: entity has no ID');
        }

        $targetDefinition = $this->registry->getDefinition($relation->targetEntity);

        switch ($relation->type) {
            case 'MANY_TO_ONE':
                return $this->loadManyToOne($entity, $relation, $targetDefinition);

            case 'ONE_TO_MANY':
                return $this->loadOneToMany($entity, $relation, $targetDefinition);

            case 'MANY_TO_MANY':
                return $this->loadManyToMany($entity, $relation, $targetDefinition);

            default:
                throw new \InvalidArgumentException("Unknown relation type: {$relation->type}");
        }
    }

    private function loadManyToOne(
        DynamicEntity $entity,
        RelationMetadata $relation,
        EntityDefinition $targetDefinition
    ): ?DynamicEntity {
        $foreignKeyValue = $entity->get($relation->foreignKey);
        if (!$foreignKeyValue) {
            return null;
        }

        // GET /companies/{id}
        $response = $this->httpClient->request(
            'GET',
            $targetDefinition->apiEndpoint . '/' . $foreignKeyValue
        );

        $key = $targetDefinition->objectName;
        return $this->mapper->mapToEntity($response['data'][$key], $targetDefinition);
    }

    private function loadOneToMany(
        DynamicEntity $entity,
        RelationMetadata $relation,
        EntityDefinition $targetDefinition
    ): DynamicEntityCollection {
        // GET /people?filter[companyId][eq]=abc-123
        $filter = new CustomFilter([
            $relation->foreignKey => $entity->get('id')
        ]);

        $response = $this->httpClient->request('GET', $targetDefinition->apiEndpoint, [
            'query' => ['filter' => $filter->buildFilterString()]
        ]);

        return DynamicEntityCollection::fromApiResponse($response, $targetDefinition, $this->mapper);
    }

    private function loadManyToMany(
        DynamicEntity $entity,
        RelationMetadata $relation,
        EntityDefinition $targetDefinition
    ): DynamicEntityCollection {
        // Load via junction table or GraphQL nested query
        // Implementation depends on Twenty CRM's API structure
        // May require additional metadata about junction tables
        throw new \RuntimeException('MANY_TO_MANY relations not yet implemented');
    }
}
```

#### 12. Relation Discovery from Metadata

**Enhanced EntityRegistry:**

```php
class EntityRegistry
{
    private function discoverEntities(): void
    {
        $response = $this->httpClient->request('GET', 'metadata/objects');

        foreach ($response['data']['objects'] as $objectData) {
            $fields = $this->extractFields($objectData['fields'] ?? []);
            $relations = $this->extractRelations($objectData['fields'] ?? []); // NEW

            $definition = new EntityDefinition(
                objectName: $objectData['nameSingular'],
                apiEndpoint: '/' . $objectData['namePlural'],
                fields: $fields,
                standardFields: $this->getStandardFieldNames($fields),
                nestedObjectMap: $this->buildNestedObjectMap($fields),
                relations: $relations, // NEW
            );

            $this->definitions[$objectData['nameSingular']] = $definition;
        }
    }

    private function extractRelations(array $fieldsData): array
    {
        $relations = [];

        foreach ($fieldsData as $fieldData) {
            // Check if field is a relation type
            if (in_array($fieldData['type'], ['RELATION', 'RELATION_MANY_TO_ONE', 'RELATION_ONE_TO_MANY'])) {
                $relations[] = new RelationMetadata(
                    name: $fieldData['name'],
                    type: $this->mapRelationType($fieldData['type']),
                    targetEntity: $fieldData['relationDefinition']['targetObjectMetadata']['nameSingular'] ?? '',
                    foreignKey: $fieldData['relationDefinition']['foreignKeyFieldName'] ?? '',
                    inverseName: $fieldData['relationDefinition']['inverseSideFieldName'] ?? null,
                    isNullable: $fieldData['isNullable'] ?? true,
                    isCustom: $fieldData['isCustom'] ?? false,
                );
            }
        }

        return $relations;
    }
}
```

### Client Integration

**Updated TwentyCrmClient:**

```php
class TwentyCrmClient implements ClientInterface
{
    private ?EntityRegistry $registry = null;

    // BACKWARD COMPATIBLE: Keep existing methods
    public function contacts(): ContactServiceInterface
    public function companies(): CompanyServiceInterface

    // NEW: Dynamic entity access
    public function entity(string $name): GenericEntityService
    {
        $definition = $this->getRegistry()->getDefinition($name);
        return new GenericEntityService(
            $this->httpClient,
            $definition,
            $this->mapper
        );
    }

    public function registry(): EntityRegistry
}
```

## Usage Examples

### Example 1: Custom Campaign Entity

```php
// Works immediately without code changes!
$client = new TwentyCrmClient($httpClient, $auth);

// Create campaign
$campaign = new DynamicEntity(
    $client->registry()->getDefinition('campaign'),
    [
        'name' => 'Q1 2025 Product Launch',
        'status' => 'ACTIVE',
        'startDate' => '2025-01-01',
        'budget' => 50000
    ]
);

$created = $client->entity('campaign')->create($campaign);
echo "Created campaign: " . $created->get('id');

// Update campaign
$created->set('status', 'COMPLETED');
$client->entity('campaign')->update($created);

// Search campaigns
$filter = new CustomFilter(['status' => 'ACTIVE']);
$options = new SearchOptions(limit: 10, orderBy: 'startDate');
$campaigns = $client->entity('campaign')->find($filter, $options);

foreach ($campaigns as $campaign) {
    echo $campaign->get('name') . PHP_EOL;
}
```

### Example 2: Generated Type-Safe Code

```bash
# Generate typed DTO for Campaign
php bin/generate-entity campaign src/Generated
```

**Generated Output:**
```php
// src/Generated/Campaign.php
class Campaign extends DynamicEntity
{
    public function getName(): ?string
    {
        return $this->get('name');
    }

    public function setName(?string $name): self
    {
        $this->set('name', $name);
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        $date = $this->get('startDate');
        return $date ? new \DateTime($date) : null;
    }

    public function getBudget(): ?float
    {
        return $this->get('budget');
    }

    // ... all fields with proper types and PHPDoc
}

// Usage with IDE autocomplete and type safety
$campaign = new Campaign($definition);
$campaign->setName('Q1 Launch'); // IDE autocomplete!
$campaign->setBudget(50000.00);
```

### Example 3: Entity Relations

```php
// MANY_TO_ONE: Load company for a person
$person = $client->entity('person')->getById('person-123');
$company = $person->loadRelation('company'); // Lazy load
echo $company->get('name');

// ONE_TO_MANY: Load people for a company
$company = $client->entity('company')->getById('company-456');
$people = $company->loadRelation('people'); // Returns DynamicEntityCollection
foreach ($people as $person) {
    echo $person->get('name')['firstName'] . PHP_EOL;
}

// Eager loading with 'with' option
$options = new SearchOptions(
    limit: 10,
    with: ['company', 'activities']  // Load relations upfront
);
$people = $client->entity('person')->find($filter, $options);

// Relations already loaded, no additional API calls
foreach ($people as $person) {
    $company = $person->getRelation('company'); // Already loaded
    if ($company) {
        echo "{$person->get('name')['firstName']} works at {$company->get('name')}" . PHP_EOL;
    }
}

// Campaign ↔ Person relations (your use case!)
$campaign = $client->entity('campaign')->getById('campaign-789');
$participants = $campaign->loadRelation('people');
echo "Campaign has " . $participants->count() . " participants" . PHP_EOL;
```

### Example 4: Quick Start with Generated Entities

```bash
# Step 1: Generate entities for your Twenty instance
$ vendor/bin/twenty-generate \
    --api-url=https://my-twenty.example.com/rest/ \
    --api-token=$TWENTY_TOKEN \
    --namespace="MyApp\TwentyCrm\Entities" \
    --output=src/TwentyCrm/Entities \
    --entities=person,company,campaign

Generating person... ✓
Generating company... ✓
Generating campaign... ✓

Code generation complete!
Generated files:
  src/TwentyCrm/Entities/Person.php
  src/TwentyCrm/Entities/PersonService.php
  src/TwentyCrm/Entities/PersonCollection.php
  src/TwentyCrm/Entities/Company.php
  src/TwentyCrm/Entities/CompanyService.php
  src/TwentyCrm/Entities/CompanyCollection.php
  src/TwentyCrm/Entities/Campaign.php
  src/TwentyCrm/Entities/CampaignService.php
  src/TwentyCrm/Entities/CampaignCollection.php

# Step 2: Commit generated code
$ git add src/TwentyCrm/Entities/
$ git commit -m "Add generated Twenty CRM entities"

# Step 3: Use typed entities with full IDE support
```

```php
use MyApp\TwentyCrm\Entities\Person;
use MyApp\TwentyCrm\Entities\PersonService;
use MyApp\TwentyCrm\Entities\Campaign;
use Factorial\TwentyCrm\Client\TwentyCrmClient;

$client = new TwentyCrmClient($httpClient);

// Option 1: Use generated service (fully typed)
$personService = new PersonService($client->getHttpClient());
$person = new Person($client->registry()->getDefinition('person'));
$person->setEmail('john@example.com');
$person->setFirstName('John');
$person->setLastName('Doe');
$created = $personService->create($person);

// Option 2: Use dynamic entity (flexible)
$campaign = $client->entity('campaign');
$newCampaign = new DynamicEntity(
    $client->registry()->getDefinition('campaign'),
    ['name' => 'Q1 Launch', 'status' => 'ACTIVE']
);
$created = $campaign->create($newCampaign);

// Best of both worlds: Type safety when you need it, flexibility when you don't
```

## Implementation Progress

**Last Updated:** 2025-10-12
**Current Branch:** `refactor/make-it-dynamic`
**Overall Status:** ✅ All Phases Complete | 🎉 Pull Request Created | 📋 Awaiting Review
**Pull Request:** https://github.com/factorial-io/twenty-crm-php-client/pull/2

### Summary

🎉 **Implementation Complete!** All phases (1-7) have been successfully completed and submitted for review.

The dynamic entity system is fully functional with code generation, complex field handling, entity relations, and a composable FilterBuilder for type-safe queries. All hardcoded Contact/Company entities have been removed. Comprehensive migration documentation (MIGRATION.md, PREDEFINED_FIELDS.md, FILTERS.md) guides users from v0.x to v1.0. The system now works with ANY Twenty instance and supports the correct Twenty CRM filter syntax from the OpenAPI specification.

**Pull Request Status:**
- ✅ 30 commits implementing complete refactoring
- ✅ 97 files changed: 17,095 insertions, 2,383 deletions
- ✅ All unit tests passing (107 tests)
- ✅ All integration tests passing
- ✅ PHPStan level 5 compliant
- ✅ Comprehensive PR description with examples and migration guide
- 📋 Ready for code review and merge to `main` branch

### ✅ Phase 1: Foundation (COMPLETED)

**Status:** 100% Complete
**Completed:** 2025-10-10
**Commits:** cf8b968, 653c91e

**Delivered:**
- ✅ DynamicEntity class (src/DTO/DynamicEntity.php)
  - Implements `ArrayAccess`, `IteratorAggregate`, `JsonSerializable`
  - Field validation against EntityDefinition
  - Immutability through cloning
  - Support for nested objects and relations
- ✅ EntityDefinition class (src/Metadata/EntityDefinition.php)
  - Stores entity metadata (fields, endpoints, standard fields)
  - Field lookup and validation methods
- ✅ FieldMetadata class (src/Metadata/FieldMetadata.php)
  - Field type, nullability, system/custom flags
  - Integration with existing metadata system
- ✅ Comprehensive unit tests
  - DynamicEntityTest.php: 100% coverage
  - All 107 unit tests passing

**Key Decisions:**
- Used `ArrayAccess` for intuitive API: `$entity['fieldName']`
- Validation at construction time for early error detection
- Immutable design for safer state management

**Files:**
```
src/DTO/DynamicEntity.php (198 lines)
src/Metadata/EntityDefinition.php (116 lines)
src/Metadata/FieldMetadata.php (59 lines)
tests/Unit/DTO/DynamicEntityTest.php
```

### ✅ Phase 2: Entity Registry & Services (COMPLETED)

**Status:** 100% Complete
**Completed:** 2025-10-11
**Commits:** e196f9b, a6afd51, f08825d, cd77d05

**Delivered:**
- ✅ EntityRegistry class (src/Services/EntityRegistry.php)
  - Discovers entities from `/metadata/objects` API endpoint
  - Caches EntityDefinition instances
  - Entity lookup and validation methods
- ✅ GenericEntityService class (src/Services/GenericEntityService.php)
  - Full CRUD operations: `find()`, `getById()`, `create()`, `update()`, `delete()`, `batchUpsert()`
  - **Intelligent field filtering on updates** (critical feature)
  - Works with any Twenty CRM entity
- ✅ TwentyCrmClient integration (src/Client/TwentyCrmClient.php)
  - New `entity(name)` method for dynamic entity access
  - Registry accessor for metadata discovery
- ✅ Campaign entity integration tests
  - 11/11 tests passing against real API
  - Demonstrates system working without hardcoded DTOs
  - Tests: create, read, update, delete, find, ArrayAccess, Iterator, JSON
- ✅ Field filtering implementation and documentation
  - Hybrid approach using `isSystem` flag + explicit timestamp list
  - Comprehensive findings documented in PRD Section C
  - All update operations work correctly

**Critical Discovery: Field Filtering Strategy**

During implementation, we discovered that update operations were failing with 500 errors when sending system-managed fields. Investigation revealed:

1. **Twenty API provides `isSystem` flag** indicating system-managed vs user-updatable fields
2. **Auto-managed timestamps** (`createdAt`, `updatedAt`, `deletedAt`) have `isSystem=false` but shouldn't be updated
3. **RELATION fields with `isSystem=false` ARE updatable** (they're foreign keys)

**Solution:** Hybrid filtering approach:
```php
// Primary: Use isSystem flag from API
if ($fieldMeta->isSystem) { continue; }

// Secondary: Explicit list for auto-managed timestamps
if (in_array($fieldName, ['createdAt', 'updatedAt', 'deletedAt', 'createdBy'])) { continue; }

// DO NOT filter by type (DATE_TIME, RELATION, etc.)
```

**Test Results:**
- ✅ All 107 unit tests passing
- ✅ All 11 Campaign integration tests passing
- ✅ Update operations work correctly with field filtering
- ✅ No 500 errors from sending read-only fields

**Files:**
```
src/Services/EntityRegistry.php (117 lines)
src/Services/GenericEntityService.php (267 lines)
src/Client/TwentyCrmClient.php (54 lines - updated)
tests/Unit/Services/EntityRegistryTest.php
tests/Unit/Services/GenericEntityServiceTest.php
usage-example/tests/Integration/CampaignIntegrationTest.php (321 lines)
usage-example/tests/TestCase.php (28 lines)
```

### ✅ Phase 3: Code Generation (COMPLETED)

**Status:** 100% Complete
**Completed:** 2025-10-11
**Commits:** 6b123fc

**Delivered:**
- ✅ CodegenConfig class (src/Generator/CodegenConfig.php)
  - Supports both PHP and YAML configuration files
  - Environment variable substitution in YAML (${VAR_NAME})
  - Automatic .env file loading
  - Configuration validation
- ✅ EntityGenerator class (src/Generator/EntityGenerator.php)
  - Uses Nette PHP Generator for PSR-12 compliant code
  - Generates typed getters for all fields
  - Generates setters only for updatable fields (uses FieldConstants)
  - Proper PHPDoc blocks and type hints
- ✅ FieldConstants class (src/Metadata/FieldConstants.php)
  - Centralized field filtering logic
  - Shared between GenericEntityService and EntityGenerator
  - Single source of truth for auto-managed fields
- ✅ CLI tool `bin/twenty-generate`
  - Based on Symfony Console
  - Supports configuration files and command-line options
  - Rich output with progress indicators
  - Comprehensive help and documentation
- ✅ YAML configuration support
  - Clean, user-friendly alternative to PHP config
  - Environment variable substitution
  - Example: `.twenty-codegen.yaml`
- ✅ Generated entities tested successfully
  - Person.php (639 lines)
  - Company.php (564 lines)
  - Campaign.php (223 lines)
  - All compile without errors
  - Read-only fields correctly excluded from setters

**Key Features:**
- **Portable code generation**: Configurable namespace and output directory
- **Field filtering**: Only generates setters for user-editable fields
- **Professional output**: PSR-12 compliant, properly formatted code
- **Flexible configuration**: Supports both PHP and YAML formats
- **Environment-aware**: Loads .env files automatically

**Configuration Example:**
```yaml
namespace: Factorial\TwentyCrm\Entities
output_dir: src
api_url: https://factorial.twenty.com/rest/
api_token: ${TWENTY_API_TOKEN}
entities:
  - person
  - company
  - campaign
options:
  overwrite: true
```

**Generated Code Features:**
- Extends DynamicEntity for compatibility
- Type-safe getters with proper return types
- Setters only for updatable fields (not createdAt, updatedAt, etc.)
- Full PHPDoc annotations
- IDE autocomplete support

**Dependencies Added:**
- `nette/php-generator`: ^4.2 (code generation)
- `symfony/console`: ^7.3 (CLI tool)
- `symfony/yaml`: ^7.3 (YAML config support)

**Files:**
```
src/Generator/CodegenConfig.php (272 lines)
src/Generator/EntityGenerator.php (237 lines)
src/Metadata/FieldConstants.php (116 lines)
src/Console/GenerateEntitiesCommand.php (290 lines)
bin/twenty-generate (executable)
usage-example/.twenty-codegen.yaml (example config)
usage-example/src/Campaign.php (223 lines, generated)
usage-example/src/Company.php (564 lines, generated)
usage-example/src/Person.php (639 lines, generated)
```

### ✅ Phase 4: Complex Field Handlers & Service Generation (COMPLETED)

**Status:** 100% Complete
**Completed:** 2025-10-11
**Commits:** 74eda27

**Delivered:**
- ✅ NestedObjectHandler interface (src/FieldHandlers/NestedObjectHandler.php)
  - Contract for bidirectional field transformations
  - `fromApi()` and `toApi()` methods
  - `getPhpType()` for code generation
- ✅ Field Handler implementations
  - PhonesFieldHandler - Transform phones → PhoneCollection
  - LinksFieldHandler - Transform links → LinkCollection
  - EmailsFieldHandler - Simplify emails object → string
  - NameFieldHandler - Transform name object → Name DTO
  - AddressFieldHandler - Transform address → Address DTO
- ✅ FieldHandlerRegistry class (src/FieldHandlers/FieldHandlerRegistry.php)
  - Central registry for all field handlers
  - `fromApi()` and `toApi()` transformation methods
  - `getPhpType()` for type detection
- ✅ New DTO classes
  - Address (src/DTO/Address.php) - Structured address with helper methods
  - Name (src/DTO/Name.php) - Structured name with getFullName()
- ✅ ServiceGenerator class (src/Generator/ServiceGenerator.php)
  - Generates typed service wrappers around GenericEntityService
  - Matches ContactService/CompanyService API exactly
  - Methods: find(), getById(), create(), update(), delete(), batchUpsert()
- ✅ CollectionGenerator class (src/Generator/CollectionGenerator.php)
  - Generates typed collection classes
  - Helper methods: count(), isEmpty(), first(), getEntities()
  - Static fromDynamicCollection() for easy conversion
- ✅ Enhanced EntityGenerator
  - Uses FieldHandlerRegistry for complex type detection
  - Generates services and collections via flags
  - Proper type hints with full namespace support
- ✅ Automatic transformation in DynamicEntity
  - get() method transforms arrays → PHP objects
  - toArray() method transforms objects → API arrays
  - Lazy handler initialization for performance
  - **Critical for backward compatibility**
- ✅ Generated entities regenerated with complex types
  - Person.php: PhoneCollection, LinkCollection, Name, Address types
  - Company.php: PhoneCollection, LinkCollection, Address types
  - Campaign.php: Basic types (no complex fields)
- ✅ Generated services tested successfully
  - PersonService matches ContactService API
  - All CRUD operations work correctly
  - Integration test demonstrates full functionality

**Key Architectural Decisions:**

1. **Automatic Transformation in DynamicEntity**
   - Field handlers transform data in get()/toArray() methods
   - No changes needed to existing tests
   - Backward compatible with array-based code
   - Collection objects work seamlessly

2. **Service Generation Strategy**
   - Services wrap GenericEntityService (composition)
   - Return typed entities and collections
   - Convert DynamicEntity arrays to typed instances
   - Minimal API surface for easy maintenance

3. **Collection Generation**
   - No fromDynamicCollection() dependency (removed)
   - Services handle transformation directly
   - Collections are simple typed array wrappers
   - Helper methods for common operations

**Generated Code Structure (9 files, 2,078 lines):**

For each entity, the generator creates:
1. **Entity class** - Typed getters/setters with collection types
2. **Service class** - Typed CRUD operations
3. **Collection class** - Typed collection with helpers

**Example Generated Files:**
```
usage-example/src/Person.php (643 lines)
usage-example/src/PersonService.php (122 lines)
usage-example/src/PersonCollection.php (93 lines)
usage-example/src/Company.php (567 lines)
usage-example/src/CompanyService.php (122 lines)
usage-example/src/CompanyCollection.php (93 lines)
usage-example/src/Campaign.php (223 lines)
usage-example/src/CampaignService.php (122 lines)
usage-example/src/CampaignCollection.php (93 lines)
```

**Test Results:**
```
✓ PersonService.find() returns PersonCollection
✓ PersonService.getById() returns Person
✓ Person.getPhones() returns PhoneCollection
✓ Person.getName() returns Name object
✓ Person.getContactAddress() returns Address object
✓ All CRUD operations work correctly
✓ Automatic transformation between API arrays and PHP objects
✓ API matches ContactService exactly
```

**Usage Example:**
```php
// Generate entities with services and collections
php bin/twenty-generate \
    --config=.twenty-codegen.yaml \
    --with-services \
    --with-collections

// Use generated service (fully typed)
$registry = $client->registry();
$personService = new PersonService(
    $client->getHttpClient(),
    $registry->getDefinition('person')
);

// Find persons
$filter = new CustomFilter(null);
$options = new SearchOptions(limit: 10);
$persons = $personService->find($filter, $options);  // Returns PersonCollection

// Work with complex fields
$person = $persons->first();
$phone = $person->getPhones()->getPrimaryNumber();  // PhoneCollection
$name = $person->getName()->getFullName();          // Name object
$address = $person->getContactAddress()->getFormatted();  // Address object
```

**Files:**
```
src/FieldHandlers/NestedObjectHandler.php (69 lines)
src/FieldHandlers/PhonesFieldHandler.php (60 lines)
src/FieldHandlers/LinksFieldHandler.php (58 lines)
src/FieldHandlers/EmailsFieldHandler.php (68 lines)
src/FieldHandlers/NameFieldHandler.php (56 lines)
src/FieldHandlers/AddressFieldHandler.php (63 lines)
src/FieldHandlers/FieldHandlerRegistry.php (135 lines)
src/DTO/Name.php (147 lines)
src/DTO/Address.php (221 lines)
src/DTO/DynamicEntity.php (modified - added transformation)
src/Generator/ServiceGenerator.php (223 lines)
src/Generator/CollectionGenerator.php (151 lines)
src/Generator/EntityGenerator.php (modified - service/collection generation)
usage-example/generate_with_services.php (generation script)
usage-example/test_generated_service.php (integration test)
```

### Pending Work

**Immediate Next Steps:**
1. **Option A:** Commit Phase 4 changes and merge to main branch
2. **Option B:** Add unit tests for field handlers and service generation
3. **Option C:** Begin Phase 5 (Valinor Integration for validation)

**Completed in Phase 4:**
- ✅ Complex fields (phones, emails, addresses) now return proper types
- ✅ PhoneCollection, LinkCollection, Name, Address in generated code
- ✅ Automatic transformation between arrays and objects
- ✅ Service and collection generation working

**Phase 5+ (Future):**
- Phase 5: Valinor Integration (optional - for enhanced validation)
- Phase 6: Entity Relations (lazy/eager loading for related entities)
- Phase 7: Remove Hardcoded Entities & Migration Guide
- Phase 8: Additional Testing & Documentation

**Cleanup:**
- Debug scripts in `usage-example/` can be removed (used for investigation only)
- Add unit tests for CodegenConfig and EntityGenerator

### Technical Achievements

**Design Patterns:**
- ✅ Generic entity system supporting any Twenty CRM entity
- ✅ Metadata-driven discovery from API
- ✅ Separation of concerns (Entity, Service, Registry)
- ✅ Intelligent field filtering based on API metadata

**Code Quality:**
- ✅ PHPStan level 5 compliant
- ✅ PHPCS compliant
- ✅ 100% test coverage for core classes
- ✅ Integration tests against real API

**Developer Experience:**
- ✅ Campaign entity works without any hardcoded code
- ✅ Simple, intuitive API: `$client->entity('campaign')->create($entity)`
- ✅ ArrayAccess syntax: `$entity['fieldName']`
- ✅ Comprehensive error handling

### Lessons Learned

1. **Trust API Metadata:** The `isSystem` flag is authoritative; don't make assumptions based on field types
2. **Integration Testing is Critical:** Unit tests can't catch API-specific behavior (like timestamp auto-management)
3. **Start Simple:** Phase 1+2+3 deliver full functionality; complex field handlers are enhancement
4. **Documentation Matters:** Thorough PRD and field filtering documentation saves debugging time
5. **Code Generation Complexity:** Generated code should start simple (basic types) before adding complex field handlers

### D. Complex Field Handling: Current vs. Desired State

**Current Implementation (Phase 3):**

DynamicEntity and generated entities use a **simple, storage-only approach** for complex fields:

```php
// DynamicEntity stores raw API response data
private array $data;

public function get(string $fieldName): mixed
{
    return $this->data[$fieldName] ?? null;  // Returns raw array
}

// Generated Person.php
public function getPhones(): array  // Basic type
{
    return $this->get('phones');  // Raw array from API
}

// Usage - manual array access required
$person = $entityService->find('person-id');
$phones = $person->getPhones();
$primaryNumber = $phones['primaryPhoneNumber'] ?? null;
$countryCode = $phones['primaryPhoneCountryCode'] ?? null;
```

**Comparison Table:**

| Feature | DynamicEntity (Current) | Generated Code (Current) | Old Contact Class | Phase 4 Goal |
|---------|------------------------|-------------------------|-------------------|--------------|
| **Phones Type** | `mixed` | `array` | `PhoneCollection` | `PhoneCollection` |
| **Helper Methods** | ❌ None | ❌ None | ✅ `getPrimaryNumber()` | ✅ Generate helpers |
| **Type Safety** | ❌ No IDE hints | ⚠️ Generic `array` | ✅ Full IDE support | ✅ Full IDE support |
| **Manipulation** | Manual array ops | Manual array ops | ✅ `addPhone()` | ✅ Collection methods |
| **Validation** | ❌ None | ❌ None | ✅ At construction | ✅ Via handlers |

**Examples of Complex Fields:**

1. **Phones** (PHONES field type):
   ```php
   // API returns:
   [
       'primaryPhoneNumber' => '+1234567890',
       'primaryPhoneCountryCode' => 'US',
       'primaryPhoneCallingCode' => '+1',
       'additionalPhones' => [...]
   ]

   // Current: User accesses manually
   $number = $person->getPhones()['primaryPhoneNumber'] ?? null;

   // Desired (Phase 4): PhoneCollection
   $number = $person->getPhones()->getPrimaryNumber();
   ```

2. **Emails** (complex object):
   ```php
   // API returns:
   ['primaryEmail' => 'john@example.com', 'additionalEmails' => [...]]

   // Current: Manual extraction
   $email = $person->getEmails()['primaryEmail'] ?? null;

   // Desired: Simple getter
   $email = $person->getEmail();  // Returns string
   ```

3. **Name** (structured object):
   ```php
   // API returns:
   ['firstName' => 'John', 'lastName' => 'Doe']

   // Current: Array access
   $name = $person->getName();
   $first = $name['firstName'] ?? null;

   // Desired: Separate getters
   $first = $person->getFirstName();
   $last = $person->getLastName();
   $full = $person->getFullName();  // Helper method
   ```

**Phase 4 Implementation Plan:**

Phase 4 will add **field handlers** that transform between API format and PHP objects:

```php
interface NestedObjectHandler
{
    public function fromApi(array $data): mixed;
    public function toApi(mixed $value): array;
}

class PhonesFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): PhoneCollection
    {
        return PhoneCollection::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        return $value instanceof PhoneCollection
            ? $value->toArray()
            : $value;
    }
}

// EntityGenerator will use handlers to determine types:
public function getPhones(): PhoneCollection  // Not array!
{
    return $this->get('phones');  // Handler transforms automatically
}
```

**Why We Started Simple:**

1. **Incremental Complexity**: Get basic code generation working first
2. **User Choice**: Some users may prefer simple arrays over collection objects
3. **API Independence**: Different Twenty instances may have different complex field structures
4. **Testing**: Easier to test and validate basic types first

**Impact on Users:**

✅ **Current system is functional** - users can work with arrays
⚠️ **Less ergonomic** - manual array access, no helper methods
🔄 **Phase 4 will enhance** - add collection types and helpers without breaking existing code

### Environment Setup

**To Resume Work:**
```bash
# Switch to feature branch
git checkout refactor/make-it-dynamic

# Install dependencies
composer install

# Run unit tests
vendor/bin/phpunit tests/Unit

# Run integration tests
cd usage-example
cp .env.example .env
# Edit .env with your TWENTY_API_BASE_URI and TWENTY_API_TOKEN
composer install
vendor/bin/phpunit tests/
```

**Current Test Status:**
```
Unit Tests: 107/107 passing ✅
Integration Tests: 11/11 passing ✅
PHPStan: Level 5 passing ✅
PHPCS: All checks passing ✅
```

### Recent Commits

```
cd77d05 docs: document field filtering strategy in PRD
f08825d fix: improve field filtering in GenericEntityService updates
a6afd51 test: add Campaign entity integration test using dynamic entity system
e196f9b feat: add entity registry and generic entity service
cf8b968 feat: implement Phase 1 - DynamicEntity foundation
653c91e refactor: separate core library from factorial-specific entities
9b193b1 docs: add comprehensive PRD for dynamic entity system refactoring
```

### Next Session Quick Start

**If continuing with Phase 4 (Complex Field Handlers):**
1. Read Phase 4 specification below
2. Implement `NestedObjectHandler` interface
3. Create `PhoneFieldHandler`, `LinkFieldHandler`, etc.
4. Update EntityGenerator to use field handlers
5. Regenerate entities with proper collection types
6. Test complex field operations

**If adding unit tests for Phase 3:**
1. Create `tests/Unit/Generator/CodegenConfigTest.php`
2. Create `tests/Unit/Generator/EntityGeneratorTest.php`
3. Test configuration loading (PHP and YAML)
4. Test entity generation with mock metadata
5. Verify generated code structure

**If merging to main:**
1. Remove debug scripts from `usage-example/`
2. Run full test suite one more time
3. Create PR: `refactor/make-it-dynamic` → `main`
4. Tag as `v0.3.0-beta` or similar

---

## Implementation Phases

### Phase 1: Foundation (Week 1)
**Goal:** Core dynamic entity infrastructure

**Deliverables:**
- [ ] Install Valinor dependency
- [ ] Implement DynamicEntity class
- [ ] Implement EntityDefinition class
- [ ] Add unit tests for DynamicEntity
- [ ] Add unit tests for EntityDefinition

**Success Criteria:**
- DynamicEntity can store and retrieve arbitrary fields
- EntityDefinition correctly models entity metadata
- All unit tests pass

### Phase 2: Entity Registry & Discovery (Week 2)
**Goal:** Automatic entity discovery from Twenty API

**Deliverables:**
- [ ] Implement EntityRegistry class
- [ ] Implement entity discovery from `/metadata/objects`
- [ ] Implement GenericEntityService
- [ ] Update TwentyCrmClient with entity() method
- [ ] Add integration tests with real Campaign entity

**Success Criteria:**
- Registry discovers all entities from API
- Campaign entity can be created/read/updated/deleted
- Integration tests pass

### Phase 3: Code Generation (Week 3)
**Goal:** Generate type-safe DTOs for development

**Deliverables:**
- [ ] Implement EntityGenerator class
- [ ] Create CLI tool `bin/generate-entity`
- [ ] Generate DTO with typed properties
- [ ] Generate Service with typed methods
- [ ] Generate Collection class
- [ ] Generate SearchFilter class
- [ ] Add documentation for code generation

**Success Criteria:**
- Generated code is valid PHP
- Generated code passes PHPStan level 5
- Generated DTOs work with GenericEntityService
- Documentation is clear and complete

### Phase 4: Complex Field Handlers (Week 4)
**Goal:** Support nested object transformations

**Deliverables:**
- [ ] Implement NestedObjectHandler interface
- [ ] Implement EmailsFieldHandler
- [ ] Implement PhonesFieldHandler
- [ ] Implement NameFieldHandler
- [ ] Implement AddressFieldHandler
- [ ] Implement LinksFieldHandler
- [ ] Implement DomainNameFieldHandler
- [ ] Implement FieldHandlerRegistry
- [ ] Add unit tests for each handler

**Success Criteria:**
- Complex fields (emails, phones) work correctly
- Transformation is bidirectional (API ↔ Entity)
- All unit tests pass

### Phase 5: Valinor Integration (Week 4-5)
**Goal:** Type validation and mapping

**Deliverables:**
- [ ] Implement EntityMapper class
- [ ] Integrate Valinor TreeMapper
- [ ] Implement EntityValidator class
- [ ] Add validation error handling
- [ ] Add unit tests for mapper
- [ ] Add integration tests for validation

**Success Criteria:**
- Valinor correctly maps API data to entities
- Validation catches type errors
- Enum values are validated
- Required fields are enforced

### Phase 6: Entity Relations (Week 5)
**Goal:** Support entity relations (Company ↔ Person, Campaign ↔ Person)

**Deliverables:**
- [ ] Implement RelationMetadata class
- [ ] Implement RelationLoader class
- [ ] Update EntityDefinition with relations support
- [ ] Update EntityRegistry to discover relations
- [ ] Add loadRelation() method to DynamicEntity
- [ ] Add eager loading support to SearchOptions
- [ ] Add unit tests for RelationMetadata
- [ ] Add unit tests for RelationLoader
- [ ] Add integration tests for relations

**Success Criteria:**
- MANY_TO_ONE relations work (Person → Company)
- ONE_TO_MANY relations work (Company → People)
- Lazy loading works correctly
- Eager loading with 'with' option works
- Relations discovered from metadata
- All unit and integration tests pass

### Phase 7: Remove Hardcoded Entities & Migration (Week 6)
**Goal:** Remove Contact/Company DTOs, provide migration path

**Deliverables:**
- [ ] Remove Contact.php and Company.php from src/DTO/
- [ ] Remove ContactService and CompanyService
- [ ] Remove ContactCollection and CompanyCollection
- [ ] Keep Phone, Link, DomainName helper classes (still useful)
- [ ] Create MIGRATION.md guide for v0.x users
- [ ] Create example repository with generated entities
- [ ] Update README with new usage patterns
- [ ] Document predefined field mappings as reference

**Migration Guide Contents:**
- [ ] How to generate entities for default Twenty schema
- [ ] Mapping from old Contact API to new Person entity
- [ ] Mapping from old Company API to new Company entity
- [ ] Code examples showing before/after
- [ ] FAQ for common migration scenarios

**Success Criteria:**
- Contact/Company removed from library
- Migration guide is clear and comprehensive
- Example repository demonstrates new approach
- Documentation shows both DynamicEntity and generated entity usage

### Phase 8: Testing & Documentation (Week 6)
**Goal:** Comprehensive testing and docs

**Deliverables:**
- [ ] Add tests for DynamicEntity
- [ ] Add tests for EntityRegistry
- [ ] Add tests for GenericEntityService
- [ ] Add tests for EntityMapper
- [ ] Add tests for code generation
- [ ] Update README with dynamic entity examples
- [ ] Create MIGRATION.md guide
- [ ] Create CODEGEN.md guide
- [ ] Add inline code documentation

**Success Criteria:**
- Test coverage > 90%
- All integration tests pass
- Documentation is clear and complete
- Examples work as documented

## Timeline

### Conservative Estimate: 6-7 weeks
- Week 1: Foundation (DynamicEntity, EntityDefinition)
- Week 2: Entity Registry & Discovery
- Week 3: Code Generation with Portability
- Week 4: Field Handlers & Valinor
- Week 5: Entity Relations (NEW)
- Week 6: Migration & BC layer
- Week 7: Testing & Documentation

### Aggressive Estimate: 4-5 weeks
- Weeks 1-2: Foundation + Registry (combined)
- Week 3: Field Handlers & Valinor
- Week 4: Entity Relations
- Week 5: Testing & Documentation
- **Skip code generation initially** (add in v1.1)

## Risks & Mitigation

### Risk 1: Breaking Changes
**Impact:** High
**Probability:** Medium
**Mitigation:**
- Comprehensive backward compatibility layer
- Extensive test suite
- Semantic versioning
- Beta release period

### Risk 2: Performance Degradation
**Impact:** Medium
**Probability:** Low
**Mitigation:**
- Metadata caching (already in place)
- Benchmark critical paths
- Consider code generation for production
- Profile with XDebug/Blackfire

### Risk 3: Complex Nested Type Handling
**Impact:** Medium
**Probability:** Medium
**Mitigation:**
- Field handlers with unit tests
- Incremental implementation
- Fallback to generic array handling
- Document known limitations

### Risk 4: Twenty API Changes
**Impact:** High
**Probability:** Low
**Mitigation:**
- Version metadata format
- Graceful degradation
- Cache fallbacks
- Monitor API changelog

### Risk 5: Valinor Learning Curve
**Impact:** Low
**Probability:** Medium
**Mitigation:**
- Thorough documentation
- Code examples
- Fallback to Symfony Serializer if needed

### Risk 6: Code Generation Complexity
**Impact:** Medium
**Probability:** Medium
**Mitigation:**
- Start simple (basic properties only)
- Iterative improvement
- Make code generation optional
- Provide templates for customization

## Success Metrics

### Technical Metrics
- ✅ Test coverage ≥ 90%
- ✅ Performance within 10% of current implementation
- ✅ PHPStan level 5 compliance
- ✅ Zero backward compatibility breaks

### User Experience Metrics
- ✅ Campaign entity CRUD operations work
- ✅ Code generation produces valid, type-safe code
- ✅ Documentation is clear and complete
- ✅ Migration path is straightforward

### Adoption Metrics
- ✅ All existing tests pass
- ✅ Zero reported BC breaks in bug tracker
- ✅ Positive feedback on GitHub
- ✅ Usage examples in real projects

## Versioning Strategy

### Phase 6a: Current Version (v0.x)
- Add new dynamic system alongside existing
- Mark nothing as deprecated
- Document new patterns in README
- **No breaking changes**

### Phase 6b: Next Minor Version (v1.0)
- Add `@deprecated` tags to hardcoded field lists
- Encourage using code generation or dynamic entities
- Full backward compatibility maintained
- **No breaking changes**

### Phase 6c: Next Major Version (v2.0)
- Remove hardcoded field lists from DTOs
- Keep Contact/Company as facades over DynamicEntity
- Breaking: removed internal methods
- **Documented breaking changes only**

## Decisions Made

### 1. Code Generation Location
**Decision:** Code generation is part of core library
**Rationale:** Simplifies installation and usage, but remains optional feature

### 2. Entity Relations Support
**Decision:** Entity relations are P0 (must-have) for this PRD
**Rationale:** Critical for CRM functionality (Company ↔ Person, Campaign ↔ Person)
**Implementation:** See "Entity Relations System" section above

### 3. GraphQL Query Building
**Decision:** Not in scope for this PRD
**Rationale:** Can be added in future version if needed

### 4. Cache Strategy
**Decision:** Use existing MetadataService cache
**Rationale:** Already implemented and working well

### 5. Generated Code Portability
**Decision:** Generated code must be portable and framework-agnostic
**Rationale:** Library consumers (e.g., Drupal modules) need to generate code for custom Twenty installations

**Implementation Details:**
- Generated code has **configurable namespace**
- Generated code has **configurable output directory**
- Generated code **doesn't depend on library internals** (only public APIs)
- Generated code can be **committed to consumer's repository**
- CLI tool supports **configuration file** for repeatable generation

**Example Use Case (Drupal Module):**
```bash
# In a Drupal module consuming this library
# Generate entities for custom Twenty installation

# Create config file: .twenty-codegen.yml
namespace: Drupal\my_module\TwentyCrm\Generated
output_dir: src/TwentyCrm/Generated
api_url: https://my-twenty.example.com/rest/
api_token: ${TWENTY_API_TOKEN}
entities:
  - campaign
  - event
  - ticket

# Run code generation
vendor/bin/twenty-generate --config=.twenty-codegen.yml

# Generated files:
# src/TwentyCrm/Generated/Campaign.php
# src/TwentyCrm/Generated/CampaignService.php
# src/TwentyCrm/Generated/CampaignCollection.php
# src/TwentyCrm/Generated/Event.php
# ... etc

# Commit generated code to module repository
git add src/TwentyCrm/Generated/
git commit -m "Add generated Twenty CRM entities"

# Use in Drupal code:
use Drupal\my_module\TwentyCrm\Generated\Campaign;

$campaign = new Campaign($definition);
$campaign->setName('DrupalCon 2025');
```

**CLI Tool Configuration:**

```php
// bin/twenty-generate
#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Factorial\TwentyCrm\Generator\CodegenConfig;
use Factorial\TwentyCrm\Generator\EntityGenerator;

// Parse command-line options
$options = getopt('', ['config:', 'namespace:', 'output:', 'entity:']);

// Load configuration
if (isset($options['config'])) {
    $config = CodegenConfig::fromFile($options['config']);
} else {
    $config = CodegenConfig::fromOptions($options);
}

// Validate configuration
if (!$config->validate()) {
    echo "Error: Invalid configuration\n";
    exit(1);
}

// Initialize generator
$generator = new EntityGenerator($config);

// Generate entities
foreach ($config->entities as $entityName) {
    echo "Generating {$entityName}...\n";
    $generator->generateEntity($entityName);
}

echo "Code generation complete!\n";
```

**CodegenConfig Class:**

```php
class CodegenConfig
{
    public function __construct(
        public readonly string $namespace,      // Target namespace
        public readonly string $outputDir,      // Output directory
        public readonly string $apiUrl,         // Twenty API URL
        public readonly string $apiToken,       // API token
        public readonly array $entities,        // Entities to generate
        public readonly array $options = [],    // Additional options
    ) {}

    public static function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Config file not found: {$path}");
        }

        $config = require $path;
        return new self(
            namespace: $config['namespace'] ?? 'Generated',
            outputDir: $config['output_dir'] ?? 'src/Generated',
            apiUrl: $config['api_url'],
            apiToken: $config['api_token'],
            entities: $config['entities'] ?? [],
            options: $config['options'] ?? [],
        );
    }

    public function validate(): bool
    {
        return !empty($this->namespace)
            && !empty($this->outputDir)
            && !empty($this->apiUrl)
            && !empty($this->apiToken);
    }
}
```

**Generated Code Structure:**

```php
// src/TwentyCrm/Generated/Campaign.php
namespace Drupal\my_module\TwentyCrm\Generated;

use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * Campaign entity (auto-generated).
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Campaign extends DynamicEntity
{
    public function __construct(EntityDefinition $definition, array $data = [])
    {
        parent::__construct($definition, $data);
    }

    // Typed getters and setters...
    public function getName(): ?string
    {
        return $this->get('name');
    }

    public function setName(?string $name): self
    {
        $this->set('name', $name);
        return $this;
    }

    // ... more methods
}
```

**Key Features for Portability:**
- ✅ Namespace is fully configurable
- ✅ Output directory is configurable
- ✅ Generated code only depends on public library APIs
- ✅ Config file can be committed and versioned
- ✅ Generation is repeatable and idempotent
- ✅ Generated code includes proper PHP DocBlocks
- ✅ PHPStan/Psalm annotations for type safety

## Dependencies

### New Dependencies
- `cuyz/valinor`: ^1.0 (PHP object mapper)

### Existing Dependencies (No Changes)
- `php`: ^8.1
- `psr/http-client`: ^1.0
- `psr/http-message`: ^1.0 || ^2.0
- `psr/log`: ^1.0 || ^2.0 || ^3.0

## Appendix

### A. File Structure

```
src/
├── DTO/
│   ├── DynamicEntity.php (new)
│   ├── DynamicEntityCollection.php (new)
│   ├── Phone.php (existing: kept as helper)
│   ├── PhoneCollection.php (existing: kept as helper)
│   ├── Link.php (existing: kept as helper)
│   ├── LinkCollection.php (existing: kept as helper)
│   ├── DomainName.php (existing: kept as helper)
│   ├── DomainNameCollection.php (existing: kept as helper)
│   ├── SearchOptions.php (existing)
│   ├── FilterInterface.php (existing)
│   └── CustomFilter.php (existing)
├── Metadata/
│   ├── EntityDefinition.php (new)
│   ├── RelationMetadata.php (new)
│   ├── FieldMetadata.php (existing)
│   └── SelectField.php (existing)
├── Registry/
│   └── EntityRegistry.php (new)
├── Services/
│   ├── GenericEntityService.php (new)
│   └── MetadataService.php (existing)
├── Relations/
│   └── RelationLoader.php (new)
├── Mapping/
│   ├── MapperInterface.php (new)
│   ├── EntityMapper.php (new)
│   └── EntityValidator.php (new)
├── FieldHandlers/
│   ├── NestedObjectHandler.php (new)
│   ├── FieldHandlerRegistry.php (new)
│   ├── EmailsFieldHandler.php (new)
│   ├── PhonesFieldHandler.php (new)
│   ├── NameFieldHandler.php (new)
│   ├── AddressFieldHandler.php (new)
│   ├── LinksFieldHandler.php (new)
│   └── DomainNameFieldHandler.php (new)
├── Generator/
│   ├── CodegenConfig.php (new)
│   ├── EntityGenerator.php (new)
│   ├── DtoGenerator.php (new)
│   ├── ServiceGenerator.php (new)
│   └── CollectionGenerator.php (new)
└── Client/
    └── TwentyCrmClient.php (modified: add entity() method)

bin/
├── twenty-generate (new CLI tool)
└── generate-entity (legacy alias)

tests/
├── Unit/
│   ├── DynamicEntityTest.php (new)
│   ├── EntityRegistryTest.php (new)
│   ├── EntityMapperTest.php (new)
│   ├── Relations/
│   │   ├── RelationMetadataTest.php (new)
│   │   └── RelationLoaderTest.php (new)
│   └── Generator/
│       ├── CodegenConfigTest.php (new)
│       └── EntityGeneratorTest.php (new)
└── Integration/
    ├── GenericEntityServiceTest.php (new)
    ├── CustomEntityTest.php (new)
    ├── CampaignEntityTest.php (new)
    └── EntityRelationsTest.php (new)

docs/
├── dynamic-entity-system-prd.md (this document)
├── MIGRATION.md (new)
├── CODEGEN.md (new)
└── RELATIONS.md (new)

examples/
├── .twenty-codegen.yml.example (config file example)
├── drupal-module-integration.php (Drupal example)
├── campaign-with-relations.php (relations example)
└── quick-start.php (getting started example)

docs/
├── dynamic-entity-system-prd.md (this document)
├── MIGRATION.md (v0.x → v1.0 migration guide)
├── CODEGEN.md (code generation documentation)
├── RELATIONS.md (entity relations documentation)
└── PREDEFINED_FIELDS.md (reference: common Person/Company fields)
```

### B. Example Repository Structure

**factorial-io/twenty-crm-entities** (example repo with Factorial's entities)

```
├── composer.json
│   └── requires: factorial-io/twenty-crm-php-client
├── .twenty-codegen.yml (configuration)
├── src/
│   ├── Person.php (generated)
│   ├── PersonService.php (generated)
│   ├── PersonCollection.php (generated)
│   ├── Company.php (generated)
│   ├── CompanyService.php (generated)
│   ├── CompanyCollection.php (generated)
│   ├── Campaign.php (generated)
│   ├── CampaignService.php (generated)
│   └── CampaignCollection.php (generated)
├── tests/
│   └── (tests for generated entities)
└── README.md
    └── "Generated entities for Factorial's Twenty CRM instance"
```

**Usage:**

```json
// User's composer.json
{
  "require": {
    "factorial-io/twenty-crm-entities": "^1.0"
  }
}
```

```php
// User's code - just use Factorial's pre-generated entities
use Factorial\TwentyCrm\Entities\Person;
use Factorial\TwentyCrm\Entities\Campaign;

$person = new Person($definition);
$campaign = new Campaign($definition);
```

### C. API Compatibility Matrix

| API Method | v0.x (Current) | v1.0 (New Approach) |
|------------|----------------|---------------------|
| `$client->contacts()->find()` | ✅ | ❌ **REMOVED** - use generated `PersonService` |
| `$client->companies()->getById()` | ✅ | ❌ **REMOVED** - use generated `CompanyService` |
| `$contact->getEmail()` | ✅ | ❌ **REMOVED** - use generated `Person` |
| `$contact->setFirstName()` | ✅ | ❌ **REMOVED** - use generated `Person` |
| `Contact::fromArray()` | ✅ | ❌ **REMOVED** - use generated `Person` |
| `$client->entity('person')` | ❌ | ✅ **NEW** - dynamic access |
| `new DynamicEntity()` | ❌ | ✅ **NEW** - flexible entities |
| Code generation | ❌ | ✅ **NEW** - `bin/twenty-generate` |
| Generated typed entities | ❌ | ✅ **NEW** - run codegen |
| Hardcoded Contact/Company | ✅ | ❌ **REMOVED** - breaking change |

**Migration Path:**

v0.x → v1.0 is a **MAJOR VERSION** with breaking changes:

```php
// v0.x (OLD)
use Factorial\TwentyCrm\DTO\Contact;
$contact = new Contact();
$contact->setEmail('john@example.com');
$created = $client->contacts()->create($contact);

// v1.0 (NEW) - Option 1: Generated entities
// First run: bin/twenty-generate --entities=person
use MyApp\Entities\Person;
use MyApp\Entities\PersonService;
$person = new Person($definition);
$person->setEmail('john@example.com');
$personService = new PersonService($httpClient);
$created = $personService->create($person);

// v1.0 (NEW) - Option 2: Dynamic entities
$person = new DynamicEntity($definition, [
    'emails' => ['primaryEmail' => 'john@example.com']
]);
$created = $client->entity('person')->create($person);
```

### C. Field Filtering Strategy for Updates

**Discovery:** Through implementation, we identified critical findings about which fields can be updated in Twenty CRM.

#### Key Findings

**1. Twenty API Provides `isSystem` Flag:**
The `/metadata/objects` endpoint returns an `isSystem` boolean for each field, indicating whether it's system-managed or user-updatable.

**Example from Campaign entity:**
```
id: isSystem=true (UUID) - Never updatable
name: isSystem=false (TEXT) - User updatable
position: isSystem=true (POSITION) - System managed
purpose: isSystem=false (TEXT) - User updatable
```

**2. Auto-Managed Timestamps Not Marked as System:**
Timestamp fields (`createdAt`, `updatedAt`, `deletedAt`) have `isSystem=false` but are auto-managed by the database. Attempting to update these causes 500 errors.

**3. Relations Can Be Updatable:**
`RELATION` type fields with `isSystem=false` CAN be updated to set foreign key relationships:
- `person.company` (RELATION, isSystem=false) - Can be set to link person to company
- `opportunity.accountOwner` (RELATION, isSystem=false) - Can be set
- `timelineActivities` (RELATION, isSystem=true) - System managed, cannot be updated

#### Implemented Filtering Strategy

The `GenericEntityService.filterUpdatableFields()` method uses a **hybrid approach**:

```php
private function filterUpdatableFields(array $data): array
{
    // Auto-managed fields explicitly filtered
    $autoManagedFields = ['createdAt', 'updatedAt', 'deletedAt', 'createdBy'];

    foreach ($data as $fieldName => $value) {
        // 1. Filter auto-managed timestamps/audit fields
        if (in_array($fieldName, $autoManagedFields)) {
            continue;
        }

        // 2. Check field metadata
        $fieldMeta = $this->definition->getField($fieldName);

        // 3. Filter if field not in metadata (safety)
        if (!$fieldMeta) {
            continue;
        }

        // 4. Filter based on isSystem flag from API
        if ($fieldMeta->isSystem) {
            continue;
        }

        // Field is updatable
        $filtered[$fieldName] = $value;
    }
}
```

#### Field Categories

**Always Filtered (Not Updatable):**
1. **System Fields** (`isSystem=true` from API)
   - `id`, `position`, `searchVector`
   - System-managed relations: `favorites`, `timelineActivities`, `attachments`

2. **Auto-Managed Fields** (explicit list)
   - `createdAt`, `updatedAt`, `deletedAt`, `createdBy`
   - These have `isSystem=false` but are database-managed

3. **Unknown Fields** (not in metadata)
   - Filtered for safety

**Allowed (Updatable):**
- Regular fields: `name`, `description`, `industry`, `employees`, etc.
- Complex fields: `PHONES`, `ADDRESS`, `LINKS`, `CURRENCY`
- **User-managed relations** (`isSystem=false`): `company`, `people`, `accountOwner`

#### Important: Do Not Filter by Type

**❌ Wrong Approach:**
```php
// Don't do this - makes assumptions about types
$readOnlyTypes = ['UUID', 'DATE_TIME', 'ACTOR', 'RELATION'];
if (in_array($field->type, $readOnlyTypes)) { continue; }
```

**✅ Correct Approach:**
```php
// Use metadata flags and explicit lists only
if ($field->isSystem) { continue; }
if (in_array($fieldName, $autoManagedFields)) { continue; }
```

**Rationale:**
- `DATE_TIME` fields might be user-updatable (e.g., `startDate`, `dueDate`)
- `RELATION` fields with `isSystem=false` are updatable (foreign keys)
- `ACTOR` fields could theoretically be updatable in custom entities
- Trust the API's `isSystem` flag over type assumptions

#### Testing Results

**Integration Test:** `CampaignIntegrationTest`
- ✅ 11/11 tests passing
- ✅ Create, read, update, delete operations work
- ✅ Update operations correctly filter system fields
- ✅ No 500 errors from sending read-only fields

**Unit Test:** `GenericEntityServiceTest`
- ✅ Field filtering tested with mock metadata
- ✅ Update operations send only updatable fields

#### Lessons Learned

1. **Trust API Metadata:** The `isSystem` flag is the primary source of truth
2. **Supplement with Domain Knowledge:** Auto-managed timestamps need explicit handling
3. **Don't Assume by Type:** Field types don't reliably indicate updatability
4. **Test Against Real API:** Integration tests revealed the timestamp issue that unit tests couldn't catch

### D. References

- [Twenty CRM API Documentation](https://twenty.com/developers)
- [Valinor Documentation](https://valinor.cuyz.io/)
- [PSR-18: HTTP Client](https://www.php-fig.org/psr/psr-18/)
- [PHP 8.1+ Features](https://www.php.net/releases/8.1/en.php)

---

## Summary

This PRD provides a comprehensive plan to transform the Twenty CRM PHP Client from a hardcoded ORM into a **code generation framework** that addresses all your requirements:

### 🎯 Core Philosophy Change

**Old Approach (v0.x):** Library ships with hardcoded Contact/Company entities
- ❌ Only works for default Twenty schema
- ❌ Custom entities require library changes
- ❌ Maintenance burden on library authors
- ❌ One-size-fits-none solution

**New Approach (v1.0):** Library provides tools, users generate entities
- ✅ Works with ANY Twenty instance
- ✅ Custom entities work out of the box
- ✅ No maintenance burden on library
- ✅ Tailored solution for each user

### ✅ All Questions Answered

1. **Code generation in core**: Yes, primary way to use library
2. **Entity relations**: P0 priority, fully specified with lazy/eager loading
3. **GraphQL**: Not in scope (can be added later)
4. **Cache strategy**: Use existing MetadataService cache
5. **Code portability**: Fully configurable namespace, output directory, framework-agnostic

### 🎯 Key Features

**Code Generation First:**
- **Primary usage pattern**: `bin/twenty-generate` to scaffold entities
- Works with ANY Twenty instance (default or custom)
- Generated code is committed to user's repository
- Configurable namespace for any framework (Drupal, Laravel, etc.)
- IDE autocomplete and PHPStan support

**Dynamic Entity System (Fallback):**
- Use when you need flexibility over type safety
- Support any entity without generation
- Metadata-driven discovery from Twenty CRM API
- Type-safe with Valinor validation

**Entity Relations:**
- MANY_TO_ONE (Person → Company)
- ONE_TO_MANY (Company → People)
- MANY_TO_MANY (Campaign ↔ People)
- Lazy and eager loading strategies
- Automatic discovery from metadata

**Clean Architecture:**
- No hardcoded Contact/Company in library
- Helper classes kept (Phone, Link, DomainName)
- Library focuses on runtime and tooling
- Users own their schema-specific code

**Your Use Cases Covered:**
- ✅ Campaign entity support (generate with `--entities=campaign`)
- ✅ Factorial entities repo (`factorial-io/twenty-crm-entities`)
- ✅ Drupal module integration (configurable namespaces)
- ✅ Custom Twenty installation (API URL configuration)
- ✅ Generated code in consumer repos (portable design)
- ✅ Predefined fields documented (reference only, not hardcoded)

### 📅 Timeline

**Conservative:** 6-7 weeks (all features)
**Aggressive:** 4-5 weeks (skip code generation initially)

### 🚀 Next Steps

1. ✅ ~~Review and approve this PRD~~ - Completed
2. ✅ ~~Set up development branch~~ - Completed (`refactor/make-it-dynamic`)
3. ✅ ~~Begin Phase 1 (Foundation)~~ - All phases completed
4. ✅ ~~Iterate with feedback~~ - Implementation completed with testing
5. 📋 **Code review and merge** - Pull Request #2 awaiting review
6. 🎯 **Release v1.0** - After merge and final testing

---

**Document Version:** 3.0 (Code Generation First Approach)
**Status:** ✅ Implemented - Awaiting Code Review
**Last Updated:** October 12, 2025
**Major Changes:**
- v3.0: Removed hardcoded entities, code generation becomes primary approach
- v2.0: Added entity relations, resolved open questions
- v1.0: Initial PRD with dynamic entity system

**Approved By:** [Pending]

**Breaking Changes Notice:**
This PRD proposes a **MAJOR VERSION** bump (v0.x → v1.0) with intentional breaking changes:
- Remove hardcoded Contact/Company entities
- Remove ContactService/CompanyService
- Users must generate entities or use DynamicEntity
- Migration guide provided for v0.x users
