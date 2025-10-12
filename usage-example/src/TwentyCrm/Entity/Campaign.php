<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

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
    public function getTimelineActivities(): ?string
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
    public function getParticipants(): ?string
    {
        return $this->get('participants');
    }

    /**
     * Set participants.
     */
    public function setParticipants(?string $value): self
    {
        $this->set('participants', $value);
        return $this;
    }

    /**
     * Get noteTargets.
     */
    public function getNoteTargets(): ?string
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
    public function getCampaignLink(): ?string
    {
        return $this->get('campaignLink');
    }

    /**
     * Set campaignLink.
     */
    public function setCampaignLink(?string $value): self
    {
        $this->set('campaignLink', $value);
        return $this;
    }

    /**
     * Get favorites.
     */
    public function getFavorites(): ?string
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
    public function getTaskTargets(): ?string
    {
        return $this->get('taskTargets');
    }

    /**
     * Get campaign.
     */
    public function getCampaign(): ?string
    {
        return $this->get('campaign');
    }

    /**
     * Set campaign.
     */
    public function setCampaign(?string $value): self
    {
        $this->set('campaign', $value);
        return $this;
    }

    /**
     * Get attachments.
     */
    public function getAttachments(): ?string
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
