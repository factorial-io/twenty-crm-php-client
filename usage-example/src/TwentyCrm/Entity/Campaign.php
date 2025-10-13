<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Campaign entity (auto-generated).
 *
 * This class provides typed access to campaign entity fields.
 * Generated from Twenty CRM metadata.
 *
 * All metadata is baked into this class at generation time,
 * so no runtime API calls are needed.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Campaign extends StaticEntity
{
    protected static function getEntityName(): string
    {
        return 'campaign';
    }

    protected static function getEntityNamePlural(): string
    {
        return 'campaigns';
    }

    protected static function getApiEndpoint(): string
    {
        return '/campaigns';
    }

    protected static function getAllFieldNames(): array
    {
        return [
            'timelineActivities',
            'name',
            'createdBy',
            'updatedAt',
            'id',
            'purpose',
            'participants',
            'noteTargets',
            'position',
            'campaignLink',
            'favorites',
            'deletedAt',
            'taskTargets',
            'campaign',
            'attachments',
            'createdAt',
            'searchVector',
            'targetGroup',
        ];
    }

    protected static function getFieldMetadata(string $fieldName): ?array
    {
        return match ($fieldName) {
            'timelineActivities' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'TimelineActivities', 'description' => 'TimelineActivities tied to the Campaign', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '02debc97-bbe3-49c0-b1af-cae60464f7e3', 'isActive' => true, 'icon' => 'IconTimelineEvent'],
            'name' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Name', 'description' => 'Name', 'defaultValue' => '\'\'', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '0884557b-a4a8-46b7-ad11-cedc0e217e1c', 'isActive' => true, 'icon' => 'IconAbc'],
            'createdBy' => ['type' => FieldType::ACTOR, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Created by', 'description' => 'The creator of the record', 'defaultValue' => array (
          'name' => '\'\'',
          'source' => '\'MANUAL\'',
        ), 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '1f624cf5-c2ec-4dfe-8d4a-ad9f8de1d33c', 'isActive' => true, 'icon' => 'IconCreativeCommonsSa'],
            'updatedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Last update', 'description' => 'Last time the record was changed', 'defaultValue' => 'now', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '3b83d408-21fd-4be6-8cc9-b870c220471a', 'isActive' => true, 'icon' => 'IconCalendarClock'],
            'id' => ['type' => FieldType::UUID, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Id', 'description' => 'Id', 'defaultValue' => 'uuid', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '53b831b5-5914-4fca-a23a-d321322dc992', 'isActive' => true, 'icon' => 'Icon123'],
            'purpose' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Purpose', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '5db55d32-66e0-448c-9f8a-d3fd4dcffea5', 'isActive' => true, 'icon' => 'IconTypography'],
            'participants' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Participants', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '745224a2-dc2e-46a5-90ff-4604298a7c0b', 'isActive' => true, 'icon' => 'IconRelationOneToMany'],
            'noteTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'NoteTargets', 'description' => 'NoteTargets tied to the Campaign', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '802e4680-59de-4653-b1f6-f006b8557c3d', 'isActive' => true, 'icon' => 'IconCheckbox'],
            'position' => ['type' => FieldType::POSITION, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Position', 'description' => 'Position', 'defaultValue' => 0, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => '9c520f10-f52a-4e0c-ad9e-d383a17a2b65', 'isActive' => true, 'icon' => 'IconHierarchy2'],
            'campaignLink' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Campaign Link', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'a0fa5d5f-6480-40a1-bfcb-db37b4794779', 'isActive' => false, 'icon' => 'IconAdCircle'],
            'favorites' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Favorites', 'description' => 'Favorites tied to the Campaign', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'a6cfeae1-fe26-4941-8589-7ed30a78efb8', 'isActive' => true, 'icon' => 'IconHeart'],
            'deletedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Deleted at', 'description' => 'Deletion date', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'ab1f9a0f-895f-4ac4-bcf3-9e084ad49fb2', 'isActive' => true, 'icon' => 'IconCalendarClock'],
            'taskTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'TaskTargets', 'description' => 'TaskTargets tied to the Campaign', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'abd59481-cefd-4bd2-8da6-a8c876de18e7', 'isActive' => true, 'icon' => 'IconCheckbox'],
            'campaign' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Campaign', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'b49857ef-78ad-437a-84d3-2f0da583ed91', 'isActive' => false, 'icon' => 'IconListNumbers'],
            'attachments' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Attachments', 'description' => 'Attachments tied to the Campaign', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'c43dd7d4-bba4-484a-b517-b450b7faeeec', 'isActive' => true, 'icon' => 'IconFileImport'],
            'createdAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Creation date', 'description' => 'Creation date', 'defaultValue' => 'now', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'da1eeec0-1297-47ed-86d3-8d5f11370f18', 'isActive' => true, 'icon' => 'IconCalendar'],
            'searchVector' => ['type' => FieldType::TS_VECTOR, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Search vector', 'description' => 'Field used for full-text search', 'defaultValue' => null, 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'eaee7325-4398-40b7-af49-2a17ba6683ce', 'isActive' => false, 'icon' => null],
            'targetGroup' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Target Group', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => '7ddbc98e-ed55-47e8-85ea-7bc1c5aea80e', 'id' => 'f15980f6-b51a-4d9b-89fa-db9162b792d1', 'isActive' => true, 'icon' => 'IconTypography'],
            default => null,
        };
    }

    protected static function getFieldToApiMap(): array
    {
        return [
            'timelineActivities' => 'timelineActivitiesId',
            'participants' => 'participantsId',
            'noteTargets' => 'noteTargetsId',
            'campaignLink' => 'campaignLinkId',
            'favorites' => 'favoritesId',
            'taskTargets' => 'taskTargetsId',
            'campaign' => 'campaignId',
            'attachments' => 'attachmentsId',
        ];
    }

    protected static function getApiToFieldMap(): array
    {
        return [
            'timelineActivitiesId' => 'timelineActivities',
            'participantsId' => 'participants',
            'noteTargetsId' => 'noteTargets',
            'campaignLinkId' => 'campaignLink',
            'favoritesId' => 'favorites',
            'taskTargetsId' => 'taskTargets',
            'campaignId' => 'campaign',
            'attachmentsId' => 'attachments',
        ];
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
