<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\Entity\Campaign;
use Factorial\TwentyCrm\Query\FilterBuilder;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration tests for generated CampaignService.
 *
 * Tests the generated Campaign entity and CampaignService from
 * usage-example/src/TwentyCrm/Entity/ and usage-example/src/TwentyCrm/Service/
 */
class CampaignServiceTest extends IntegrationTestCase
{

    public function testCreateCampaign(): void
    {
        $this->requireClient();

        // Create a campaign using generated Campaign entity
        $campaign = $this->campaignService->createInstance();
        $campaign->setName($this->generateTestName('Campaign'));
        $campaign->setPurpose('Integration test campaign');
        $campaign->setTargetGroup('Test Users');

        $created = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $created->getId());

        $this->assertNotNull($created->getId());
        $this->assertEquals($campaign->getName(), $created->getName());
        $this->assertEquals($campaign->getPurpose(), $created->getPurpose());
        $this->assertEquals($campaign->getTargetGroup(), $created->getTargetGroup());
    }

    public function testGetCampaignById(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->campaignService->createInstance();
        $campaign->setName($this->generateTestName('GetById'));
        $campaign->setPurpose('Test campaign for getById');
        $campaign->setTargetGroup('Test Users');

        $created = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $created->getId());

        // Retrieve by ID
        $retrieved = $this->campaignService->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertEquals($created->getId(), $retrieved->getId());
        $this->assertEquals($created->getName(), $retrieved->getName());
        $this->assertEquals($created->getPurpose(), $retrieved->getPurpose());
    }

    public function testUpdateCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->campaignService->createInstance();
        $campaign->setName($this->generateTestName('Update'));
        $campaign->setPurpose('Original purpose');
        $campaign->setTargetGroup('Test Users');

        $created = $this->campaignService->create($campaign);
        $this->trackResource('campaign', $created->getId());

        // Update the campaign
        $created->setPurpose('Updated purpose');
        $updated = $this->campaignService->update($created);

        $this->assertEquals($created->getId(), $updated->getId());
        $this->assertEquals('Updated purpose', $updated->getPurpose());

        // Verify via retrieval
        $retrieved = $this->campaignService->getById($updated->getId());
        $this->assertEquals('Updated purpose', $retrieved->getPurpose());
    }

    public function testDeleteCampaign(): void
    {
        $this->requireClient();

        // Create a campaign
        $campaign = $this->campaignService->createInstance();
        $campaign->setName($this->generateTestName('Delete'));
        $campaign->setPurpose('Campaign to be deleted');
        $campaign->setTargetGroup('Test Users');

        $created = $this->campaignService->create($campaign);
        $campaignId = $created->getId();

        // Delete the campaign
        $result = $this->campaignService->delete($campaignId);
        $this->assertTrue($result);

        // Verify it's deleted
        $retrieved = $this->campaignService->getById($campaignId);
        $this->assertNull($retrieved);
    }

    public function testFindCampaigns(): void
    {
        $this->requireClient();

        // Create multiple campaigns with distinct names
        $testPrefix = $this->generateTestName('Find');
        $createdCampaigns = [];

        for ($i = 1; $i <= 3; $i++) {
            $campaign = $this->campaignService->createInstance();
            $campaign->setName("{$testPrefix}_{$i}");
            $campaign->setPurpose("Test campaign {$i}");
            $campaign->setTargetGroup('Test Users');

            $created = $this->campaignService->create($campaign);
            $this->trackResource('campaign', $created->getId());
            $createdCampaigns[] = $created;
        }

        // Find campaigns
        $filter = FilterBuilder::create()->build();
        $options = new SearchOptions();
        $results = $this->campaignService->find($filter, $options);

        // Should find at least our 3 campaigns
        $this->assertGreaterThanOrEqual(3, $results->count());

        // Verify our campaigns are in the results
        $foundCampaigns = $results->getCampaigns();
        $foundIds = array_map(fn($c) => $c->getId(), $foundCampaigns);
        foreach ($createdCampaigns as $campaign) {
            $this->assertContains($campaign->getId(), $foundIds);
        }
    }

    public function testGetNonExistentCampaignReturnsNull(): void
    {
        $this->requireClient();

        $result = $this->campaignService->getById('non-existent-campaign-id-12345');

        $this->assertNull($result);
    }

    public function testDeleteNonExistentCampaignReturnsFalse(): void
    {
        $this->requireClient();

        $result = $this->campaignService->delete('non-existent-campaign-id-12345');

        $this->assertFalse($result);
    }
}
