<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\Query\FilterBuilder;
use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\Enums\FieldType;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\EnumOption;
use Factorial\TwentyCrm\Metadata\SelectField;
use PHPUnit\Framework\TestCase;

class FilterBuilderTest extends TestCase
{
    public function testCreateEmptyBuilder(): void
    {
        $builder = FilterBuilder::create();

        $this->assertInstanceOf(FilterBuilder::class, $builder);
        $this->assertFalse($builder->hasFilters());
        $this->assertNull($builder->buildFilterString());
    }

    public function testSimpleEqualsCondition(): void
    {
        $builder = FilterBuilder::create()
            ->equals('name', 'John');

        $this->assertTrue($builder->hasFilters());
        $this->assertEquals('name[eq]:"John"', $builder->buildFilterString());
    }

    public function testMultipleConditionsWithAnd(): void
    {
        $builder = FilterBuilder::create()
            ->equals('firstName', 'John')
            ->equals('lastName', 'Doe');

        $expected = 'firstName[eq]:"John",lastName[eq]:"Doe"';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testMultipleConditionsWithOr(): void
    {
        $builder = FilterBuilder::create()
            ->useOr()
            ->equals('status', 'ACTIVE')
            ->equals('status', 'PENDING');

        $expected = 'or(status[eq]:"ACTIVE",status[eq]:"PENDING")';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testDotNotationFields(): void
    {
        $builder = FilterBuilder::create()
            ->equals('name.firstName', 'John')
            ->equals('address.city', 'San Francisco');

        $expected = 'name.firstName[eq]:"John",address.city[eq]:"San Francisco"';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testNumericComparison(): void
    {
        $builder = FilterBuilder::create()
            ->greaterThan('age', 18)
            ->lessThanOrEquals('salary', 100000);

        $expected = 'age[gt]:18,salary[lte]:100000';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testBooleanValues(): void
    {
        $builder = FilterBuilder::create()
            ->equals('isActive', true)
            ->equals('isDeleted', false);

        $expected = 'isActive[eq]:true,isDeleted[eq]:false';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testNullValues(): void
    {
        $builder = FilterBuilder::create()
            ->isNull('deletedAt')
            ->isNotNull('email');

        $expected = 'deletedAt[is]:NULL,email[neq]:NULL';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testInOperator(): void
    {
        $builder = FilterBuilder::create()
            ->in('status', ['ACTIVE', 'PENDING', 'COMPLETED']);

        $expected = 'status[in]:["ACTIVE","PENDING","COMPLETED"]';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testStringOperators(): void
    {
        $builder1 = FilterBuilder::create()->contains('email', '@example.com');
        $this->assertEquals('email[ilike]:"%@example.com%"', $builder1->buildFilterString());

        $builder2 = FilterBuilder::create()->startsWith('name', 'Jo');
        $this->assertEquals('name[startsWith]:"Jo"', $builder2->buildFilterString());
    }

    public function testAllComparisonOperators(): void
    {
        $builder = FilterBuilder::create();

        $builder->where('a', 'eq', 1);
        $builder->where('b', 'neq', 2);
        $builder->where('c', 'gt', 3);
        $builder->where('d', 'gte', 4);
        $builder->where('e', 'lt', 5);
        $builder->where('f', 'lte', 6);

        $expected = 'a[eq]:1,b[neq]:2,c[gt]:3,d[gte]:4,e[lt]:5,f[lte]:6';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testBuildReturnsCustomFilter(): void
    {
        $builder = FilterBuilder::create()
            ->equals('name', 'John');

        $filter = $builder->build();

        $this->assertInstanceOf(CustomFilter::class, $filter);
        $this->assertEquals('name[eq]:"John"', $filter->buildFilterString());
    }

    public function testClearConditions(): void
    {
        $builder = FilterBuilder::create()
            ->equals('name', 'John')
            ->equals('age', 30);

        $this->assertTrue($builder->hasFilters());

        $builder->clear();

        $this->assertFalse($builder->hasFilters());
        $this->assertNull($builder->buildFilterString());
    }

    public function testInvalidOperatorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operator: invalid');

        FilterBuilder::create()->where('field', 'invalid', 'value');
    }

    public function testInvalidLogicalOperatorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid logical operator');

        FilterBuilder::create()->setLogicalOperator('INVALID');
    }

    public function testEscapingDoubleQuotes(): void
    {
        $builder = FilterBuilder::create()
            ->equals('description', 'This is a "quoted" string');

        $expected = 'description[eq]:"This is a \\"quoted\\" string"';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    public function testForEntityStaticFactory(): void
    {
        $definition = $this->createMockEntityDefinition();
        $builder = FilterBuilder::forEntity($definition);

        $this->assertInstanceOf(FilterBuilder::class, $builder);
        $this->assertSame($definition, $builder->getDefinition());
    }

    public function testValidationWithSelectField(): void
    {
        $definition = $this->createMockEntityDefinition();
        $builder = FilterBuilder::forEntity($definition);

        // Valid value - should not throw
        $builder->equals('status', 'ACTIVE');
        $this->assertTrue($builder->hasFilters());

        // Invalid value - should throw
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value \'INVALID\' for SELECT field \'status\'');

        FilterBuilder::forEntity($definition)->equals('status', 'INVALID');
    }

    public function testValidationWithUnknownField(): void
    {
        $definition = $this->createMockEntityDefinition();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown field: unknown');

        FilterBuilder::forEntity($definition)->equals('unknown', 'value');
    }

    public function testValidationWithSelectFieldInOperator(): void
    {
        $definition = $this->createMockEntityDefinition();

        // Valid values - should not throw
        $builder1 = FilterBuilder::forEntity($definition)
            ->in('status', ['ACTIVE', 'PENDING']);
        $this->assertTrue($builder1->hasFilters());

        // Invalid value in array - should throw
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value \'INVALID\' in array for SELECT field \'status\'');

        FilterBuilder::forEntity($definition)->in('status', ['ACTIVE', 'INVALID']);
    }

    public function testHelperMethods(): void
    {
        $builder = FilterBuilder::create();

        // Test all helper methods exist and work
        $builder->equals('a', 1);
        $builder->notEquals('b', 2);
        $builder->greaterThan('c', 3);
        $builder->greaterThanOrEquals('d', 4);
        $builder->lessThan('e', 5);
        $builder->lessThanOrEquals('f', 6);
        $builder->in('g', [7, 8]);
        $builder->contains('h', '9');
        $builder->startsWith('i', '10');
        $builder->isNull('k');
        $builder->isNotNull('l');

        $this->assertCount(11, $builder->getConditions());
    }

    public function testFluentInterface(): void
    {
        $builder = FilterBuilder::create()
            ->equals('a', 1)
            ->greaterThan('b', 2)
            ->contains('c', 'test')
            ->useOr()
            ->isNull('d');

        $this->assertInstanceOf(FilterBuilder::class, $builder);
        $this->assertTrue($builder->hasFilters());
    }

    public function testComplexRealWorldExample(): void
    {
        $builder = FilterBuilder::create()
            ->equals('name.firstName', 'John')
            ->contains('emails.primaryEmail', '@example.com')
            ->in('status', ['ACTIVE', 'PENDING'])
            ->greaterThanOrEquals('createdAt', '2025-01-01')
            ->isNotNull('companyId');

        $expected = 'name.firstName[eq]:"John",emails.primaryEmail[ilike]:"%@example.com%",status[in]:["ACTIVE","PENDING"],createdAt[gte]:"2025-01-01",companyId[neq]:NULL';
        $this->assertEquals($expected, $builder->buildFilterString());
    }

    /**
     * Create a mock EntityDefinition for testing.
     *
     * @return EntityDefinition
     */
    private function createMockEntityDefinition(): EntityDefinition
    {
        $statusField = new SelectField(
            id: 'status-id',
            name: 'status',
            type: FieldType::SELECT,
            label: 'Status',
            objectMetadataId: 'object-id',
            isNullable: false,
            options: [
                new EnumOption('ACTIVE', 'Active', 'green', 0),
                new EnumOption('PENDING', 'Pending', 'yellow', 1),
                new EnumOption('INACTIVE', 'Inactive', 'red', 2),
            ]
        );

        return new EntityDefinition(
            objectName: 'testEntity',
            objectNamePlural: 'testEntities',
            apiEndpoint: '/test-entities',
            fields: ['status' => $statusField],
            standardFields: ['status'],
            relations: []
        );
    }
}
