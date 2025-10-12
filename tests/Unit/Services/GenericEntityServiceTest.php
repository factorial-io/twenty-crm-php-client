<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\Services;

use Factorial\TwentyCrm\Query\CustomFilter;
use Factorial\TwentyCrm\Entity\DynamicEntity;
use Factorial\TwentyCrm\Collection\DynamicEntityCollection;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Enums\FieldType;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Tests\TestCase;

/**
 * Unit tests for GenericEntityService.
 *
 * @covers \Factorial\TwentyCrm\Services\GenericEntityService
 */
class GenericEntityServiceTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private EntityDefinition $definition;
    private GenericEntityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);

        // Create mock field metadata for testing
        $fields = [
            'name' => new class (
                'field-1',
                'name',
                FieldType::TEXT,
                'Name',
                'obj-1',
                false
            ) extends \Factorial\TwentyCrm\Metadata\FieldMetadata {
            },
            'email' => new class (
                'field-2',
                'email',
                FieldType::EMAILS,
                'Email',
                'obj-1',
                true
            ) extends \Factorial\TwentyCrm\Metadata\FieldMetadata {
            },
        ];

        $this->definition = new EntityDefinition(
            objectName: 'person',
            objectNamePlural: 'people',
            apiEndpoint: '/people',
            fields: $fields,
            standardFields: ['id', 'name', 'email'],
        );
        $this->service = new GenericEntityService($this->httpClient, $this->definition);
    }

    public function testGetDefinition(): void
    {
        $definition = $this->service->getDefinition();

        $this->assertSame($this->definition, $definition);
    }

    public function testFind(): void
    {
        $filter = new CustomFilter();
        $options = new SearchOptions();

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/people',
                $this->callback(function ($options) {
                    return isset($options['query']);
                })
            )
            ->willReturn([
                'data' => [
                    'people' => [
                        ['id' => '1', 'name' => 'John Doe'],
                        ['id' => '2', 'name' => 'Jane Smith'],
                    ],
                ],
            ]);

        $collection = $this->service->find($filter, $options);

        $this->assertInstanceOf(DynamicEntityCollection::class, $collection);
        $this->assertCount(2, $collection);
        $entities = $collection->getEntities();
        $this->assertInstanceOf(DynamicEntity::class, $entities[0]);
        $this->assertSame('John Doe', $entities[0]->get('name'));
        $this->assertInstanceOf(DynamicEntity::class, $entities[1]);
        $this->assertSame('Jane Smith', $entities[1]->get('name'));
    }

    public function testFindWithFilter(): void
    {
        $filter = CustomFilter::fromString('name eq "John"');
        $options = new SearchOptions();

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/people',
                $this->callback(function ($options) use ($filter) {
                    return isset($options['query']['filter'])
                        && $options['query']['filter'] === $filter->buildFilterString();
                })
            )
            ->willReturn([
                'data' => [
                    'people' => [
                        ['id' => '1', 'name' => 'John Doe'],
                    ],
                ],
            ]);

        $collection = $this->service->find($filter, $options);

        $this->assertInstanceOf(DynamicEntityCollection::class, $collection);
        $this->assertCount(1, $collection);
        $entities = $collection->getEntities();
        $this->assertSame('John Doe', $entities[0]->get('name'));
    }

    public function testFindReturnsEmptyArrayWhenNoResults(): void
    {
        $filter = new CustomFilter();
        $options = new SearchOptions();

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn([
                'data' => [
                    'people' => [],
                ],
            ]);

        $collection = $this->service->find($filter, $options);

        $this->assertInstanceOf(DynamicEntityCollection::class, $collection);
        $this->assertTrue($collection->isEmpty());
    }

    public function testGetById(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', '/people/123')
            ->willReturn([
                'data' => [
                    'person' => [
                        'id' => '123',
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ],
            ]);

        $entity = $this->service->getById('123');

        $this->assertInstanceOf(DynamicEntity::class, $entity);
        $this->assertSame('123', $entity->getId());
        $this->assertSame('John Doe', $entity->get('name'));
        $this->assertSame('john@example.com', $entity->get('email'));
    }

    public function testGetByIdReturnsNullWhen404(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', '/people/999')
            ->willThrowException(new ApiException('Not found', 404));

        $entity = $this->service->getById('999');

        $this->assertNull($entity);
    }

    public function testGetByIdReturnsNullWhen400(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', '/people/invalid')
            ->willThrowException(new ApiException('Bad request', 400));

        $entity = $this->service->getById('invalid');

        $this->assertNull($entity);
    }

    public function testGetByIdThrowsOnOtherErrors(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', '/people/123')
            ->willThrowException(new ApiException('Server error', 500));

        $this->service->getById('123');
    }

    public function testCreate(): void
    {
        $entity = new DynamicEntity($this->definition, [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/people',
                $this->callback(function ($options) {
                    return isset($options['json'])
                        && $options['json']['name'] === 'John Doe'
                        && isset($options['json']['email']['primaryEmail'])
                        && $options['json']['email']['primaryEmail'] === 'john@example.com';
                })
            )
            ->willReturn([
                'data' => [
                    'createPerson' => [
                        'id' => '123',
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ],
            ]);

        $createdEntity = $this->service->create($entity);

        $this->assertInstanceOf(DynamicEntity::class, $createdEntity);
        $this->assertSame('123', $createdEntity->getId());
        $this->assertSame('John Doe', $createdEntity->get('name'));
    }

    public function testUpdate(): void
    {
        $entity = new DynamicEntity($this->definition, [
            'id' => '123',
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
        ]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                '/people/123',
                $this->callback(function ($options) {
                    // Verify ID is not in the body
                    return isset($options['json'])
                        && !isset($options['json']['id'])
                        && $options['json']['name'] === 'John Updated';
                })
            )
            ->willReturn([
                'data' => [
                    'updatePerson' => [
                        'id' => '123',
                        'name' => 'John Updated',
                        'email' => 'john.updated@example.com',
                    ],
                ],
            ]);

        $updatedEntity = $this->service->update($entity);

        $this->assertInstanceOf(DynamicEntity::class, $updatedEntity);
        $this->assertSame('123', $updatedEntity->getId());
        $this->assertSame('John Updated', $updatedEntity->get('name'));
    }

    public function testUpdateThrowsWhenEntityHasNoId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity must have an ID to be updated');

        $entity = new DynamicEntity($this->definition, [
            'name' => 'John Doe',
        ]);

        $this->service->update($entity);
    }

    public function testDelete(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', '/people/123')
            ->willReturn([]);

        $result = $this->service->delete('123');

        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhen404(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', '/people/999')
            ->willThrowException(new ApiException('Not found', 404));

        $result = $this->service->delete('999');

        $this->assertFalse($result);
    }

    public function testDeleteReturnsFalseWhen400(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', '/people/invalid')
            ->willThrowException(new ApiException('Bad request', 400));

        $result = $this->service->delete('invalid');

        $this->assertFalse($result);
    }

    public function testDeleteThrowsOnOtherErrors(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', '/people/123')
            ->willThrowException(new ApiException('Server error', 500));

        $this->service->delete('123');
    }

    public function testBatchUpsert(): void
    {
        $entities = [
            new DynamicEntity($this->definition, ['name' => 'John Doe']),
            new DynamicEntity($this->definition, ['name' => 'Jane Smith']),
        ];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/batch/people',
                $this->callback(function ($options) {
                    return isset($options['json']['data'])
                        && count($options['json']['data']) === 2
                        && $options['json']['data'][0]['name'] === 'John Doe'
                        && $options['json']['data'][1]['name'] === 'Jane Smith';
                })
            )
            ->willReturn([
                'data' => [
                    'people' => [
                        ['id' => '1', 'name' => 'John Doe'],
                        ['id' => '2', 'name' => 'Jane Smith'],
                    ],
                ],
            ]);

        $collection = $this->service->batchUpsert($entities);

        $this->assertInstanceOf(DynamicEntityCollection::class, $collection);
        $this->assertCount(2, $collection);
        $upsertedEntities = $collection->getEntities();
        $this->assertSame('1', $upsertedEntities[0]->getId());
        $this->assertSame('2', $upsertedEntities[1]->getId());
    }

    public function testParseEntityResponseHandlesDifferentResponseFormats(): void
    {
        // Test GET format (data.person)
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', '/people/123')
            ->willReturn([
                'data' => [
                    'person' => [
                        'id' => '123',
                        'name' => 'John Doe',
                    ],
                ],
            ]);

        $entity = $this->service->getById('123');
        $this->assertSame('123', $entity->getId());
    }

    public function testParseEntityResponseHandlesCreateFormat(): void
    {
        // Test POST format (data.createPerson)
        $entity = new DynamicEntity($this->definition, ['name' => 'John']);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', '/people', $this->anything())
            ->willReturn([
                'data' => [
                    'createPerson' => [
                        'id' => '123',
                        'name' => 'John',
                    ],
                ],
            ]);

        $created = $this->service->create($entity);
        $this->assertSame('123', $created->getId());
    }

    public function testParseEntityResponseHandlesUpdateFormat(): void
    {
        // Test PATCH format (data.updatePerson)
        $entity = new DynamicEntity($this->definition, ['id' => '123', 'name' => 'John Updated']);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('PATCH', '/people/123', $this->anything())
            ->willReturn([
                'data' => [
                    'updatePerson' => [
                        'id' => '123',
                        'name' => 'John Updated',
                    ],
                ],
            ]);

        $updated = $this->service->update($entity);
        $this->assertSame('John Updated', $updated->get('name'));
    }
}
