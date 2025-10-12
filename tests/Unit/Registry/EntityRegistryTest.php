<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\Registry;

use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\MetadataService;
use Factorial\TwentyCrm\Tests\TestCase;

/**
 * Unit tests for EntityRegistry.
 *
 * @covers \Factorial\TwentyCrm\Registry\EntityRegistry
 */
class EntityRegistryTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private MetadataService $metadata;
    private EntityRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->metadata = $this->createMock(MetadataService::class);
        $this->registry = new EntityRegistry($this->httpClient, $this->metadata);
    }

    public function testGetDefinitionReturnsNullForUnknownEntity(): void
    {
        // Mock empty response
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn(['data' => ['objects' => []]]);

        $definition = $this->registry->getDefinition('nonexistent');

        $this->assertNull($definition);
    }

    public function testGetDefinitionReturnsEntityDefinition(): void
    {
        // Mock API response with person entity
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-123',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [
                                [
                                    'id' => 'field-1',
                                    'name' => 'name',
                                    'type' => 'TEXT',
                                    'label' => 'Name',
                                    'isNullable' => false,
                                    'isCustom' => false,
                                ],
                                [
                                    'id' => 'field-2',
                                    'name' => 'email',
                                    'type' => 'EMAILS',
                                    'label' => 'Email',
                                    'isNullable' => true,
                                    'isCustom' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $definition = $this->registry->getDefinition('person');

        $this->assertInstanceOf(EntityDefinition::class, $definition);
        $this->assertSame('person', $definition->objectName);
        $this->assertSame('people', $definition->objectNamePlural);
        $this->assertSame('/people', $definition->apiEndpoint);
        $this->assertCount(2, $definition->fields);
        $this->assertTrue($definition->hasField('name'));
        $this->assertTrue($definition->hasField('email'));
    }

    public function testHasEntityReturnsTrueForExistingEntity(): void
    {
        // Mock API response
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-123',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        $this->assertTrue($this->registry->hasEntity('person'));
    }

    public function testHasEntityReturnsFalseForNonexistentEntity(): void
    {
        // Mock empty response
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn(['data' => ['objects' => []]]);

        $this->assertFalse($this->registry->hasEntity('campaign'));
    }

    public function testGetAllEntityNames(): void
    {
        // Mock API response with multiple entities
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-1',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                        [
                            'id' => 'obj-2',
                            'nameSingular' => 'company',
                            'namePlural' => 'companies',
                            'fields' => [],
                        ],
                        [
                            'id' => 'obj-3',
                            'nameSingular' => 'campaign',
                            'namePlural' => 'campaigns',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        $names = $this->registry->getAllEntityNames();

        $this->assertCount(3, $names);
        $this->assertContains('person', $names);
        $this->assertContains('company', $names);
        $this->assertContains('campaign', $names);
    }

    public function testGetAllDefinitions(): void
    {
        // Mock API response
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-1',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                        [
                            'id' => 'obj-2',
                            'nameSingular' => 'company',
                            'namePlural' => 'companies',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        $definitions = $this->registry->getAllDefinitions();

        $this->assertCount(2, $definitions);
        $this->assertArrayHasKey('person', $definitions);
        $this->assertArrayHasKey('company', $definitions);
        $this->assertInstanceOf(EntityDefinition::class, $definitions['person']);
        $this->assertInstanceOf(EntityDefinition::class, $definitions['company']);
    }

    public function testClearCache(): void
    {
        // Mock API response for first discovery
        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-1',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        $this->metadata->expects($this->once())
            ->method('clearCache');

        // First call discovers entities
        $this->assertTrue($this->registry->hasEntity('person'));

        // Clear cache
        $this->registry->clearCache();

        // Second call re-discovers entities (proves cache was cleared)
        $this->assertTrue($this->registry->hasEntity('person'));
    }

    public function testDiscoveryOnlyHappensOnce(): void
    {
        // Mock API response - should only be called once
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-1',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        // Multiple calls should not trigger multiple API requests
        $this->registry->hasEntity('person');
        $this->registry->getDefinition('person');
        $this->registry->getAllEntityNames();
    }

    public function testExtractsRelations(): void
    {
        // Mock API response with relation field
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-123',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [
                                [
                                    'id' => 'field-1',
                                    'name' => 'company',
                                    'type' => 'RELATION',
                                    'label' => 'Company',
                                    'isNullable' => true,
                                    'isCustom' => false,
                                    'relation' => [
                                        'type' => 'MANY_TO_ONE',
                                        'sourceObjectMetadata' => [
                                            'nameSingular' => 'person',
                                            'namePlural' => 'people',
                                        ],
                                        'targetObjectMetadata' => [
                                            'nameSingular' => 'company',
                                            'namePlural' => 'companies',
                                        ],
                                        'targetFieldMetadata' => [
                                            'name' => 'people',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $definition = $this->registry->getDefinition('person');

        $this->assertNotNull($definition);
        $this->assertTrue($definition->hasRelation('company'));

        $relation = $definition->getRelation('company');
        $this->assertNotNull($relation);
        $this->assertSame('company', $relation->name);
        $this->assertSame('Company', $relation->label);
        $this->assertTrue($relation->isManyToOne());
        $this->assertSame('person', $relation->sourceObjectName);
        $this->assertSame('company', $relation->targetObjectName);
        $this->assertSame('people', $relation->targetFieldName);
    }

    public function testSkipsInvalidObjects(): void
    {
        // Mock API response with invalid objects
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            // Missing nameSingular - should be skipped
                            'id' => 'obj-1',
                            'namePlural' => 'people',
                            'fields' => [],
                        ],
                        [
                            // Missing namePlural - should be skipped
                            'id' => 'obj-2',
                            'nameSingular' => 'company',
                            'fields' => [],
                        ],
                        [
                            // Valid object
                            'id' => 'obj-3',
                            'nameSingular' => 'campaign',
                            'namePlural' => 'campaigns',
                            'fields' => [],
                        ],
                    ],
                ],
            ]);

        $names = $this->registry->getAllEntityNames();

        // Only the valid object should be registered
        $this->assertCount(1, $names);
        $this->assertContains('campaign', $names);
    }

    public function testGracefullyHandlesApiErrors(): void
    {
        // Mock API error
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willThrowException(new \Exception('API Error'));

        // Should not throw, just return empty
        $definition = $this->registry->getDefinition('person');

        $this->assertNull($definition);
        $this->assertSame([], $this->registry->getAllEntityNames());
    }

    public function testExtractsStandardFields(): void
    {
        // Mock API response with standard and custom fields
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'metadata/objects')
            ->willReturn([
                'data' => [
                    'objects' => [
                        [
                            'id' => 'obj-123',
                            'nameSingular' => 'person',
                            'namePlural' => 'people',
                            'fields' => [
                                [
                                    'id' => 'field-1',
                                    'name' => 'name',
                                    'type' => 'TEXT',
                                    'label' => 'Name',
                                    'isNullable' => false,
                                    'isCustom' => false,
                                ],
                                [
                                    'id' => 'field-2',
                                    'name' => 'customField',
                                    'type' => 'TEXT',
                                    'label' => 'Custom Field',
                                    'isNullable' => true,
                                    'isCustom' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $definition = $this->registry->getDefinition('person');

        $this->assertNotNull($definition);
        $this->assertContains('name', $definition->standardFields);
        $this->assertNotContains('customField', $definition->standardFields);
    }
}
