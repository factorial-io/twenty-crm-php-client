<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Tests\TestCase;

/**
 * Unit tests for DynamicEntity.
 *
 * @covers \Factorial\TwentyCrm\DTO\DynamicEntity
 */
class DynamicEntityTest extends TestCase
{
    private EntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a minimal EntityDefinition for testing
        $this->definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: [],
            standardFields: ['id', 'name', 'email'],
            nestedObjectMap: [],
            relations: [],
        );
    }

    public function testConstruct(): void
    {
        $entity = new DynamicEntity($this->definition);

        $this->assertInstanceOf(DynamicEntity::class, $entity);
        $this->assertSame($this->definition, $entity->getDefinition());
        $this->assertSame([], $entity->toArray());
    }

    public function testConstructWithData(): void
    {
        $data = ['id' => '123', 'name' => 'John', 'email' => 'john@example.com'];
        $entity = new DynamicEntity($this->definition, $data);

        $this->assertSame($data, $entity->toArray());
    }

    public function testGetAndSet(): void
    {
        $entity = new DynamicEntity($this->definition);

        // Test set and get
        $entity->set('name', 'John');
        $this->assertSame('John', $entity->get('name'));

        // Test get non-existent field
        $this->assertNull($entity->get('nonexistent'));
    }

    public function testHas(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John']);

        $this->assertTrue($entity->has('name'));
        $this->assertFalse($entity->has('email'));
    }

    public function testUnset(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John', 'email' => 'john@example.com']);

        $entity->unset('name');

        $this->assertFalse($entity->has('name'));
        $this->assertTrue($entity->has('email'));
    }

    public function testGetFieldNames(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John', 'email' => 'john@example.com']);

        $fieldNames = $entity->getFieldNames();

        $this->assertCount(2, $fieldNames);
        $this->assertContains('name', $fieldNames);
        $this->assertContains('email', $fieldNames);
    }

    public function testGetIdAndSetId(): void
    {
        $entity = new DynamicEntity($this->definition);

        // Test set and get ID
        $entity->setId('123');
        $this->assertSame('123', $entity->getId());

        // Test set null ID
        $entity->setId(null);
        $this->assertNull($entity->getId());
        $this->assertFalse($entity->has('id'));
    }

    public function testFromArray(): void
    {
        $data = ['id' => '123', 'name' => 'John'];
        $entity = DynamicEntity::fromArray($data, $this->definition);

        $this->assertInstanceOf(DynamicEntity::class, $entity);
        $this->assertSame($data, $entity->toArray());
        $this->assertSame($this->definition, $entity->getDefinition());
    }

    public function testToArray(): void
    {
        $data = ['id' => '123', 'name' => 'John', 'email' => 'john@example.com'];
        $entity = new DynamicEntity($this->definition, $data);

        $this->assertSame($data, $entity->toArray());
    }

    // ====================================================================
    // Relation Tests
    // ====================================================================

    public function testGetRelation(): void
    {
        $entity = new DynamicEntity($this->definition);

        // Initially no relation
        $this->assertNull($entity->getRelation('company'));
        $this->assertFalse($entity->hasLoadedRelation('company'));
    }

    public function testSetRelation(): void
    {
        $entity = new DynamicEntity($this->definition);
        $relatedEntity = new DynamicEntity($this->definition, ['id' => '456', 'name' => 'Acme Corp']);

        $entity->setRelation('company', $relatedEntity);

        $this->assertTrue($entity->hasLoadedRelation('company'));
        $this->assertSame($relatedEntity, $entity->getRelation('company'));
    }

    public function testGetLoadedRelations(): void
    {
        $entity = new DynamicEntity($this->definition);
        $company = new DynamicEntity($this->definition, ['id' => '456']);
        $manager = new DynamicEntity($this->definition, ['id' => '789']);

        $entity->setRelation('company', $company);
        $entity->setRelation('manager', $manager);

        $relations = $entity->getLoadedRelations();

        $this->assertCount(2, $relations);
        $this->assertSame($company, $relations['company']);
        $this->assertSame($manager, $relations['manager']);
    }

    // ====================================================================
    // ArrayAccess Tests
    // ====================================================================

    public function testArrayAccessOffsetExists(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John']);

        $this->assertTrue(isset($entity['name']));
        $this->assertFalse(isset($entity['email']));
    }

    public function testArrayAccessOffsetGet(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John']);

        $this->assertSame('John', $entity['name']);
        $this->assertNull($entity['email']);
    }

    public function testArrayAccessOffsetSet(): void
    {
        $entity = new DynamicEntity($this->definition);

        $entity['name'] = 'John';

        $this->assertSame('John', $entity['name']);
        $this->assertSame('John', $entity->get('name'));
    }

    public function testArrayAccessOffsetUnset(): void
    {
        $entity = new DynamicEntity($this->definition, ['name' => 'John', 'email' => 'john@example.com']);

        unset($entity['name']);

        $this->assertFalse(isset($entity['name']));
        $this->assertTrue(isset($entity['email']));
    }

    // ====================================================================
    // IteratorAggregate Tests
    // ====================================================================

    public function testIteratorAggregate(): void
    {
        $data = ['id' => '123', 'name' => 'John', 'email' => 'john@example.com'];
        $entity = new DynamicEntity($this->definition, $data);

        $iterated = [];
        foreach ($entity as $key => $value) {
            $iterated[$key] = $value;
        }

        $this->assertSame($data, $iterated);
    }

    // ====================================================================
    // JsonSerializable Tests
    // ====================================================================

    public function testJsonSerialize(): void
    {
        $data = ['id' => '123', 'name' => 'John', 'email' => 'john@example.com'];
        $entity = new DynamicEntity($this->definition, $data);

        $json = json_encode($entity);
        $decoded = json_decode($json, true);

        $this->assertSame($data, $decoded);
    }
}
