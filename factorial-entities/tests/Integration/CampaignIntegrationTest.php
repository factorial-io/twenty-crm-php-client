<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for Campaign entity using dynamic entity system.
 *
 * This test demonstrates how to work with any custom entity without
 * hardcoded DTOs, using the EntityRegistry and GenericEntityService.
 */
class CampaignIntegrationTest extends IntegrationTestCase
{
    private ?GenericEntityService $campaignService = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->client) {
            // Get the campaign service dynamically
            $this->campaignService = $this->client->entity('campaign');
        }
    }

    protected function tearDown(): void
    {
        // Clean up campaigns created during tests
        foreach (array_reverse($this->createdResources) as $resource) {
            if ($resource['type'] === 'campaign') {
                try {
                    $this->campaignService?->delete($resource['id']);
                } catch (\Exception $e) {
                    // Ignore cleanup errors - resource may already be deleted
                }
            }
        }

        parent::tearDown();
    }

    public function testEntityRegistryDiscoversCampaign(): void
    {
        $this->requireClient();

        // Verify that campaign entity is discovered
        $registry = $this->client->registry();

        $this->assertTrue($registry->hasEntity('campaign'));

        $definition = $registry->getDefinition('campaign');
        $this->assertNotNull($definition);
        $this->assertSame('campaign', $definition->objectName);
        $this->assertSame('campaigns', $definition->objectNamePlural);
        $this->assertSame('/campaigns', $definition->apiEndpoint);
    }

    public function testCreateCampaign(): void
    {
        $this->requireClient();

        // Create a campaign using DynamicEntity
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('TestCampaign'),
                'description' => 'Integration test campaign for dynamic entity system',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        $this->assertNotNull($createdCampaign->getId());
        $this->assertSame($campaign->get('name'), $createdCampaign->get('name'));
        $this->assertSame($campaign->get('description'), $createdCampaign->get('description'));
    }

    public function testGetCampaignById(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('GetByIdTest'),
                'description' => 'Test campaign for getById',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Retrieve by ID
        $retrievedCampaign = $this->campaignService->getById($createdCampaign->getId());

        $this->assertNotNull($retrievedCampaign);
        $this->assertSame($createdCampaign->getId(), $retrievedCampaign->getId());
        $this->assertSame($createdCampaign->get('name'), $retrievedCampaign->get('name'));
        $this->assertSame($createdCampaign->get('description'), $retrievedCampaign->get('description'));
    }

    public function testUpdateCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('UpdateTest'),
                'description' => 'Original description',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Update the campaign
        $createdCampaign->set('description', 'Updated description');
        $updatedCampaign = $this->campaignService->update($createdCampaign);

        $this->assertSame($createdCampaign->getId(), $updatedCampaign->getId());
        $this->assertSame('Updated description', $updatedCampaign->get('description'));

        // Verify via retrieval
        $retrievedCampaign = $this->campaignService->getById($updatedCampaign->getId());
        $this->assertSame('Updated description', $retrievedCampaign->get('description'));
    }

    public function testDeleteCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('DeleteTest'),
                'description' => 'Campaign to be deleted',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $campaignId = $createdCampaign->getId();

        // Delete the campaign
        $result = $this->campaignService->delete($campaignId);
        $this->assertTrue($result);

        // Verify it's deleted
        $retrievedCampaign = $this->campaignService->getById($campaignId);
        $this->assertNull($retrievedCampaign);
    }

    public function testFindCampaigns(): void
    {
        $this->requireClient();

        // Create multiple campaigns with distinct names
        $testPrefix = $this->generateTestName('FindTest');
        $campaigns = [];

        for ($i = 1; $i <= 3; $i++) {
            $campaign = new DynamicEntity(
                $this->campaignService->getDefinition(),
                [
                    'name' => "{$testPrefix}_{$i}",
                    'description' => "Test campaign {$i}",
                ]
            );

            $createdCampaign = $this->campaignService->create($campaign);
            $this->trackResource('campaign', $createdCampaign->getId());
            $campaigns[] = $createdCampaign;
        }

        // Find campaigns (without filter to get all)
        $filter = new CustomFilter();
        $options = new SearchOptions();
        $foundCampaigns = $this->campaignService->find($filter, $options);

        // Should find at least our 3 campaigns
        $this->assertGreaterThanOrEqual(3, count($foundCampaigns));

        // Verify our campaigns are in the results
        $foundIds = array_map(fn($c) => $c->getId(), $foundCampaigns);
        foreach ($campaigns as $campaign) {
            $this->assertContains($campaign->getId(), $foundIds);
        }
    }

    public function testCampaignArrayAccess(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('ArrayAccessTest'),
                'description' => 'Test array access on dynamic entity',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test ArrayAccess interface
        $this->assertSame($createdCampaign->get('name'), $createdCampaign['name']);
        $this->assertSame($createdCampaign->get('description'), $createdCampaign['description']);

        // Test modification via ArrayAccess
        $createdCampaign['description'] = 'Modified via array access';
        $this->assertSame('Modified via array access', $createdCampaign->get('description'));
    }

    public function testCampaignIteration(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('IterationTest'),
                'description' => 'Test iteration on dynamic entity',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test IteratorAggregate interface
        $fields = [];
        foreach ($createdCampaign as $key => $value) {
            $fields[$key] = $value;
        }

        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('description', $fields);
        $this->assertSame($createdCampaign->getId(), $fields['id']);
    }

    public function testCampaignJsonSerialization(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = new DynamicEntity(
            $this->campaignService->getDefinition(),
            [
                'name' => $this->generateTestName('JsonTest'),
                'description' => 'Test JSON serialization',
            ]
        );

        $createdCampaign = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test JSON serialization
        $json = json_encode($createdCampaign);
        $this->assertNotFalse($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('id', $decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertSame($createdCampaign->getId(), $decoded['id']);
        $this->assertSame($createdCampaign->get('name'), $decoded['name']);
    }

    public function testGetByIdReturnsNullForNonexistent(): void
    {
        $this->requireClient();

        // Try to get a non-existent campaign
        $campaign = $this->campaignService->getById('00000000-0000-0000-0000-000000000000');

        $this->assertNull($campaign);
    }

    public function testDeleteReturnsFalseForNonexistent(): void
    {
        $this->requireClient();

        // Try to delete a non-existent campaign
        $result = $this->campaignService->delete('00000000-0000-0000-0000-000000000000');

        $this->assertFalse($result);
    }
}
