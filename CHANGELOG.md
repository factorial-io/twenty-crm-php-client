# Changelog

All notable changes to this project will be documented in this file.

## [0.4.1] - 2025-10-12

- feat: add optional data parameter to createInstance() method
- docs: make AGENTS.md more welcoming and helpful for AI assistants
- docs: add AGENTS.md with codebase guidance for AI agents
- docs: update changelog for 0.4.0

## [0.4.0] - 2025-10-12

- docs: update documentation to reflect v0.4 namespace reorganization
- style: fix PSR-12 coding standard violations
- fix: address security and quality issues in filter, loader, and registry
- refactor: reorganize codebase into dedicated namespaces for better separation of concerns
- chore: remove PHP 8.1 from GitHub workflows
- fix: align FilterBuilder tests with Twenty CRM API operators
- feat: add comprehensive PSR-3 logging support
- chore: remove unused classes and update config references
- refactor: remove unused CompanyFactory and ContactFactory
- chore: enhance phpcs and phpstan configurations
- fix: align FilterBuilder with Twenty CRM API and fix integration tests
- fix: use collection->count() instead of count(collection)
- fix: create new Name object instead of mutating in testUpdatePerson
- fix: use fully qualified class name for Collection::first() return type
- fix: correct API usage in integration tests
- test: fix PersonCompanyRelation and FilterBuilder integration tests
- test: refactor Phone, LeadSource, and Company integration tests
- test: refactor integration tests to use generated entities
- refactor: return DynamicEntityCollection from service methods
- docs: update PRD with implementation completion and PR status
- feat: add metadata service for field introspection and enum validation
- test: add person-company relation integration tests
- test: add integration test infrastructure and examples
- test: fix EntityRegistryTest field type and relation metadata
- fix: implement field name mapping for RELATION fields
- refactor: use FieldType enum instead of strings in field handlers
- test: update IntegrationTestCase to use generated entities
- fix: use fully qualified class names in generated service type hints
- feat: add createInstance() method to generated services
- docs: add example YAML config file for code generation
- refactor: organize generated code into subdirectories with namespaces
- feat: add EmailCollection and Currency specialized classes
- refactor: rename factorial-entities to usage-example
- docs: update PRD to reflect Phase 7 and FilterBuilder completion
- feat: remove hardcoded Contact/Company entities and add migration guide (Phase 7)
- feat: add composable FilterBuilder with Twenty CRM filter syntax
- "Claude Code Review workflow"
- "Claude PR Assistant workflow"
- feat: add entity relations and enum types (Phase 6)
- docs: update PRD with Phase 4 commit hash
- feat: add service generation and complex field handlers (Phase 4)
- docs: update PRD with Phase 3 completion and complex field handling analysis
- feat: add YAML configuration support for code generation
- feat: implement Phase 3 - code generation for typed entities
- docs: add implementation progress section to PRD
- docs: document field filtering strategy in PRD
- fix: improve field filtering in GenericEntityService updates
- test: add Campaign entity integration test using dynamic entity system
- feat: add entity registry and generic entity service
- feat: implement Phase 1 - DynamicEntity foundation
- refactor: separate core library from factorial-specific entities
- docs: add comprehensive PRD for dynamic entity system refactoring
- feat: add metadata service for field introspection and enum validation
- test: add person-company relation integration tests
- fix: resolve phpcs warnings and apply code style fixes
- chore: add PHPStan static analysis with level 5
- docs: clarify Packagist publishing in release workflow
- docs: add release creation instructions to README
- docs: update changelog for 0.3.0

## [0.3.0] - 2025-10-08

- feat: add domain name handling and company CRUD operations
- docs: add historical entries to changelog for versions 0.1.0-0.2.0
- docs: update changelog for 0.2.1

## [0.2.1] - 2025-10-08

- ci: add automated release workflow

## [0.2.0] - 2025-10-08

- ci: add GitHub Actions workflow for code quality checks
- chore: add code quality tools (PHPCS + PHP CS Fixer) with PSR-2
- feat: add mobilePhones and social link support to Contact DTO
- docs: add comprehensive testing documentation
- test: add integration tests for Contact, Company, and Phone operations
- test: add comprehensive unit tests for Contact and Phone DTOs
- refactor: update Contact DTO to use PhoneCollection instead of string
- feat: add Phone and PhoneCollection DTOs for structured phone data
- fix: correct API response parsing and error handling in ContactService
- feat: add comprehensive test infrastructure with unit and integration tests

## [0.1.1] - 2025-08-22

- feat: Enhance Company DTO with Link collections and improve ContactService
- feat: Implement lazy loading collections and enhance DTOs
- feat: Start with CRUD for people

## [0.1.0] - 2025-08-07

- feat: initialize twenty crm php client package

