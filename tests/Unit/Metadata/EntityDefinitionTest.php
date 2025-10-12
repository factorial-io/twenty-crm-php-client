<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\Metadata;

use Factorial\TwentyCrm\Enums\FieldType;
use Factorial\TwentyCrm\Enums\RelationType;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\FieldMetadata;
use Factorial\TwentyCrm\Metadata\RelationMetadata;
use Factorial\TwentyCrm\Tests\TestCase;

/**
 * Unit tests for EntityDefinition.
 *
 * @covers \Factorial\TwentyCrm\Metadata\EntityDefinition
 */
class EntityDefinitionTest extends TestCase
{
    private function createMockFieldMetadata(string $name, bool $isNullable = true): FieldMetadata
    {
        // Create anonymous class extending FieldMetadata for testing
        return new class ($name, $isNullable) extends FieldMetadata {
            public function __construct(string $name, bool $isNullable)
            {
                parent::__construct(
                    id: 'field-' . $name,
                    name: $name,
                    type: FieldType::TEXT,
                    label: ucfirst($name),
                    objectMetadataId: 'obj-123',
                    isNullable: $isNullable,
                );
            }
        };
    }

    public function testConstruct(): void
    {
        $fields = [
            'name' => $this->createMockFieldMetadata('name'),
            'email' => $this->createMockFieldMetadata('email'),
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: ['id', 'name', 'email'],
        );

        $this->assertSame('person', $definition->objectName);
        $this->assertSame('people', $definition->objectNamePlural);
        $this->assertSame('/people', $definition->apiEndpoint);
        $this->assertSame($fields, $definition->fields);
        $this->assertSame(['id', 'name', 'email'], $definition->standardFields);
        $this->assertSame([], $definition->nestedObjectMap);
        $this->assertSame([], $definition->relations);
    }

    public function testGetField(): void
    {
        $nameField = $this->createMockFieldMetadata('name');
        $fields = ['name' => $nameField];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: [],
        );

        $this->assertSame($nameField, $definition->getField('name'));
        $this->assertNull($definition->getField('nonexistent'));
    }

    public function testHasField(): void
    {
        $fields = ['name' => $this->createMockFieldMetadata('name')];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: [],
        );

        $this->assertTrue($definition->hasField('name'));
        $this->assertFalse($definition->hasField('email'));
    }

    public function testGetFieldNames(): void
    {
        $fields = [
            'name' => $this->createMockFieldMetadata('name'),
            'email' => $this->createMockFieldMetadata('email'),
            'phone' => $this->createMockFieldMetadata('phone'),
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: [],
        );

        $fieldNames = $definition->getFieldNames();

        $this->assertCount(3, $fieldNames);
        $this->assertContains('name', $fieldNames);
        $this->assertContains('email', $fieldNames);
        $this->assertContains('phone', $fieldNames);
    }

    public function testGetRequiredFields(): void
    {
        $fields = [
            'name' => $this->createMockFieldMetadata('name', false), // required
            'email' => $this->createMockFieldMetadata('email', true), // nullable
            'phone' => $this->createMockFieldMetadata('phone', false), // required
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: [],
        );

        $requiredFields = $definition->getRequiredFields();

        $this->assertCount(2, $requiredFields);
        $this->assertArrayHasKey('name', $requiredFields);
        $this->assertArrayHasKey('phone', $requiredFields);
        $this->assertArrayNotHasKey('email', $requiredFields);
    }

    public function testGetCustomFields(): void
    {
        $fields = [
            'name' => $this->createMockFieldMetadata('name'),
            'email' => $this->createMockFieldMetadata('email'),
            'customField' => $this->createMockFieldMetadata('customField'),
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: ['id', 'name', 'email'], // customField is not in standard
        );

        $customFields = $definition->getCustomFields();

        $this->assertCount(1, $customFields);
        $this->assertArrayHasKey('customField', $customFields);
        $this->assertArrayNotHasKey('name', $customFields);
        $this->assertArrayNotHasKey('email', $customFields);
    }

    public function testIsStandardField(): void
    {
        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: ['id', 'name', 'email'],
        );

        $this->assertTrue($definition->isStandardField('name'));
        $this->assertTrue($definition->isStandardField('email'));
        $this->assertFalse($definition->isStandardField('customField'));
    }

    public function testIsCustomField(): void
    {
        $fields = [
            'name' => $this->createMockFieldMetadata('name'),
            'customField' => $this->createMockFieldMetadata('customField'),
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: ['id', 'name', 'email'],
        );

        $this->assertTrue($definition->isCustomField('customField'));
        $this->assertFalse($definition->isCustomField('name'));
        $this->assertFalse($definition->isCustomField('nonexistent')); // doesn't exist at all
    }

    public function testGetRelation(): void
    {
        $companyRelation = new RelationMetadata(
            name: 'company',
            label: 'Company',
            type: RelationType::MANY_TO_ONE,
            sourceObjectName: 'person',
            targetObjectName: 'company',
            targetFieldName: 'personCollection',
        );

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: [],
            nestedObjectMap: [],
            relations: ['company' => $companyRelation],
        );

        $this->assertSame($companyRelation, $definition->getRelation('company'));
        $this->assertNull($definition->getRelation('nonexistent'));
    }

    public function testHasRelation(): void
    {
        $companyRelation = new RelationMetadata(
            name: 'company',
            label: 'Company',
            type: RelationType::MANY_TO_ONE,
            sourceObjectName: 'person',
            targetObjectName: 'company',
            targetFieldName: 'personCollection',
        );

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: [],
            nestedObjectMap: [],
            relations: ['company' => $companyRelation],
        );

        $this->assertTrue($definition->hasRelation('company'));
        $this->assertFalse($definition->hasRelation('manager'));
    }

    public function testGetRelations(): void
    {
        $companyRelation = new RelationMetadata(
            name: 'company',
            label: 'Company',
            type: RelationType::MANY_TO_ONE,
            sourceObjectName: 'person',
            targetObjectName: 'company',
            targetFieldName: 'personCollection',
        );

        $managerRelation = new RelationMetadata(
            name: 'manager',
            label: 'Manager',
            type: RelationType::MANY_TO_ONE,
            sourceObjectName: 'person',
            targetObjectName: 'person',
            targetFieldName: 'directReports',
        );

        $relations = [
            'company' => $companyRelation,
            'manager' => $managerRelation,
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: [],
            nestedObjectMap: [],
            relations: $relations,
        );

        $this->assertSame($relations, $definition->getRelations());
    }

    public function testGetRelationNames(): void
    {
        $relations = [
            'company' => new RelationMetadata('company', 'Company', RelationType::MANY_TO_ONE, 'person', 'company', 'personCollection'),
            'manager' => new RelationMetadata('manager', 'Manager', RelationType::MANY_TO_ONE, 'person', 'person', 'directReports'),
        ];

        $definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: [],
            nestedObjectMap: [],
            relations: $relations,
        );

        $relationNames = $definition->getRelationNames();

        $this->assertCount(2, $relationNames);
        $this->assertContains('company', $relationNames);
        $this->assertContains('manager', $relationNames);
    }
}
