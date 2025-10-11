<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * Campaign entity (auto-generated).
 *
 * This class provides typed access to campaign entity fields.
 * Generated from Twenty CRM metadata.
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

    /**
     * Get timelineActivities.
     */
    public function getTimelineActivities(): mixed
    {
        return $this->get('timelineActivities');
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->get('name');
    }

    /**
     * Set name.
     */
    public function setName(string $value): self
    {
        $this->set('name', $value);
        return $this;
    }

    /**
     * Get createdBy.
     */
    public function getCreatedBy(): mixed
    {
        return $this->get('createdBy');
    }

    /**
     * Get updatedAt.
     */
    public function getUpdatedAt(): string
    {
        return $this->get('updatedAt');
    }

    /**
     * Get id.
     */
    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * Get purpose.
     */
    public function getPurpose(): string
    {
        return $this->get('purpose');
    }

    /**
     * Set purpose.
     */
    public function setPurpose(string $value): self
    {
        $this->set('purpose', $value);
        return $this;
    }

    /**
     * Get participants.
     */
    public function getParticipants(): mixed
    {
        return $this->get('participants');
    }

    /**
     * Set participants.
     */
    public function setParticipants(mixed $value): self
    {
        $this->set('participants', $value);
        return $this;
    }

    /**
     * Get noteTargets.
     */
    public function getNoteTargets(): mixed
    {
        return $this->get('noteTargets');
    }

    /**
     * Get position.
     */
    public function getPosition(): mixed
    {
        return $this->get('position');
    }

    /**
     * Get campaignLink.
     */
    public function getCampaignLink(): mixed
    {
        return $this->get('campaignLink');
    }

    /**
     * Set campaignLink.
     */
    public function setCampaignLink(mixed $value): self
    {
        $this->set('campaignLink', $value);
        return $this;
    }

    /**
     * Get favorites.
     */
    public function getFavorites(): mixed
    {
        return $this->get('favorites');
    }

    /**
     * Get deletedAt.
     */
    public function getDeletedAt(): ?string
    {
        return $this->get('deletedAt');
    }

    /**
     * Get taskTargets.
     */
    public function getTaskTargets(): mixed
    {
        return $this->get('taskTargets');
    }

    /**
     * Get campaign.
     */
    public function getCampaign(): mixed
    {
        return $this->get('campaign');
    }

    /**
     * Set campaign.
     */
    public function setCampaign(mixed $value): self
    {
        $this->set('campaign', $value);
        return $this;
    }

    /**
     * Get attachments.
     */
    public function getAttachments(): mixed
    {
        return $this->get('attachments');
    }

    /**
     * Get createdAt.
     */
    public function getCreatedAt(): string
    {
        return $this->get('createdAt');
    }

    /**
     * Get searchVector.
     */
    public function getSearchVector(): mixed
    {
        return $this->get('searchVector');
    }

    /**
     * Get targetGroup.
     */
    public function getTargetGroup(): string
    {
        return $this->get('targetGroup');
    }

    /**
     * Set targetGroup.
     */
    public function setTargetGroup(string $value): self
    {
        $this->set('targetGroup', $value);
        return $this;
    }
}
