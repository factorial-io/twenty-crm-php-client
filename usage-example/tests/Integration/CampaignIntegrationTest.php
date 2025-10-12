<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\CustomFilter;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Entity\Campaign;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for Campaign entity using generated entity system.
 *
 * This test demonstrates how to work with custom entities using
 * the generated Campaign entity and CampaignService.
 */
class CampaignIntegrationTest extends IntegrationTestCase
{

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

        // Create a campaign using generated Campaign entity
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('TestCampaign'));
        $campaign->setPurpose('Integration test campaign for dynamic entity system');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        $this->assertNotNull($createdCampaign->getId());
        $this->assertSame($campaign->getName(), $createdCampaign->getName());
        $this->assertSame($campaign->getPurpose(), $createdCampaign->getPurpose());
        $this->assertSame($campaign->getTargetGroup(), $createdCampaign->getTargetGroup());
    }

    public function testGetCampaignById(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('GetByIdTest'));
        $campaign->setPurpose('Test campaign for getById');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Retrieve by ID
        $retrievedCampaign = $this->getCampaignService()->getById($createdCampaign->getId());

        $this->assertNotNull($retrievedCampaign);
        $this->assertSame($createdCampaign->getId(), $retrievedCampaign->getId());
        $this->assertSame($createdCampaign->getName(), $retrievedCampaign->getName());
        $this->assertSame($createdCampaign->getPurpose(), $retrievedCampaign->getPurpose());
    }

    public function testUpdateCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('UpdateTest'));
        $campaign->setPurpose('Original purpose');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Update the campaign
        $createdCampaign->setPurpose('Updated purpose');
        $updatedCampaign = $this->getCampaignService()->update($createdCampaign);

        $this->assertSame($createdCampaign->getId(), $updatedCampaign->getId());
        $this->assertSame('Updated purpose', $updatedCampaign->getPurpose());

        // Verify via retrieval
        $retrievedCampaign = $this->getCampaignService()->getById($updatedCampaign->getId());
        $this->assertSame('Updated purpose', $retrievedCampaign->getPurpose());
    }

    public function testDeleteCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('DeleteTest'));
        $campaign->setPurpose('Campaign to be deleted');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $campaignId = $createdCampaign->getId();

        // Delete the campaign
        $result = $this->getCampaignService()->delete($campaignId);
        $this->assertTrue($result);

        // Verify it's deleted
        $retrievedCampaign = $this->getCampaignService()->getById($campaignId);
        $this->assertNull($retrievedCampaign);
    }

    public function testFindCampaigns(): void
    {
        $this->requireClient();

        // Create multiple campaigns with distinct names
        $testPrefix = $this->generateTestName('FindTest');
        $campaigns = [];

        for ($i = 1; $i <= 3; $i++) {
            $campaign = $this->getCampaignService()->createInstance();
            $campaign->setName("{$testPrefix}_{$i}");
            $campaign->setPurpose("Test campaign {$i}");
            $campaign->setTargetGroup('Test Users');

            $createdCampaign = $this->getCampaignService()->create($campaign);
            $this->trackResource('campaign', $createdCampaign->getId());
            $campaigns[] = $createdCampaign;
        }

        // Find campaigns (without filter to get all)
        $filter = new CustomFilter([]);
        $options = new SearchOptions();
        $foundCampaigns = $this->getCampaignService()->find($filter, $options);

        // Should find at least our 3 campaigns
        $this->assertGreaterThanOrEqual(3, $foundCampaigns->count());

        // Verify our campaigns are in the results
        $foundIds = array_map(fn($c) => $c->getId(), $foundCampaigns->getEntities());
        foreach ($campaigns as $campaign) {
            $this->assertContains($campaign->getId(), $foundIds);
        }
    }

    public function testCampaignArrayAccess(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('ArrayAccessTest'));
        $campaign->setPurpose('Test array access on dynamic entity');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test ArrayAccess interface (Campaign extends DynamicEntity which implements ArrayAccess)
        $this->assertSame($createdCampaign->getName(), $createdCampaign['name']);
        $this->assertSame($createdCampaign->getPurpose(), $createdCampaign['purpose']);

        // Test modification via ArrayAccess
        $createdCampaign['purpose'] = 'Modified via array access';
        $this->assertSame('Modified via array access', $createdCampaign->getPurpose());
    }

    public function testCampaignIteration(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('IterationTest'));
        $campaign->setPurpose('Test iteration on dynamic entity');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test IteratorAggregate interface (Campaign extends DynamicEntity which implements IteratorAggregate)
        $fields = [];
        foreach ($createdCampaign as $key => $value) {
            $fields[$key] = $value;
        }

        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('purpose', $fields);
        $this->assertSame($createdCampaign->getId(), $fields['id']);
    }

    public function testCampaignJsonSerialization(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->getCampaignService()->createInstance();
        $campaign->setName($this->generateTestName('JsonTest'));
        $campaign->setPurpose('Test JSON serialization');
        $campaign->setTargetGroup('Test Users');

        $createdCampaign = $this->getCampaignService()->create($campaign);
        $this->trackResource('campaign', $createdCampaign->getId());

        // Test JSON serialization
        $json = json_encode($createdCampaign);
        $this->assertNotFalse($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('id', $decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertSame($createdCampaign->getId(), $decoded['id']);
        $this->assertSame($createdCampaign->getName(), $decoded['name']);
    }

    public function testGetByIdReturnsNullForNonexistent(): void
    {
        $this->requireClient();

        // Try to get a non-existent campaign
        $campaign = $this->getCampaignService()->getById('00000000-0000-0000-0000-000000000000');

        $this->assertNull($campaign);
    }

    public function testDeleteReturnsFalseForNonexistent(): void
    {
        $this->requireClient();

        // Try to delete a non-existent campaign
        $result = $this->getCampaignService()->delete('00000000-0000-0000-0000-000000000000');

        $this->assertFalse($result);
    }
}
