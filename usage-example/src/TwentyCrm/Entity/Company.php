<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\Collection\LinkCollection;
use Factorial\TwentyCrm\Collection\PhoneCollection;
use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\Currency;
use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Company entity (auto-generated).
 *
 * This class provides typed access to company entity fields.
 * Generated from Twenty CRM metadata.
 *
 * All metadata is baked into this class at generation time,
 * so no runtime API calls are needed.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Company extends StaticEntity
{
    protected static function getEntityName(): string
    {
        return 'company';
    }

    protected static function getEntityNamePlural(): string
    {
        return 'companies';
    }

    protected static function getApiEndpoint(): string
    {
        return '/companies';
    }

    protected static function getAllFieldNames(): array
    {
        return [
            'originalTrafficSource',
            'id',
            'favorites',
            'linkedinBio',
            'searchVector',
            'name',
            'position',
            'createdBy',
            'timezone',
            'isPublic',
            'opportunities',
            'accountOwner',
            'timelineActivities',
            'phones',
            'facebook',
            'linkedinLink',
            'taskTargets',
            'noteTargets',
            'xLink',
            'address',
            'annualRecurringRevenue',
            'hubspotId',
            'industry',
            'yearFounded',
            'updatedAt',
            'lastActivityDate',
            'createdAt',
            'employees',
            'annualRevenue',
            'deletedAt',
            'description',
            'idealCustomerProfile',
            'domainName',
            'people',
            'attachments',
            'lifecycleStage',
        ];
    }

    protected static function getFieldMetadata(string $fieldName): ?array
    {
        return match ($fieldName) {
            'originalTrafficSource' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Original Traffic Source', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '015a35d1-fe51-4062-9dc9-ab638dbe1a30', 'isActive' => false, 'icon' => 'IconTag'],
            'id' => ['type' => FieldType::UUID, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Id', 'description' => 'Id', 'defaultValue' => 'uuid', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '037728b1-d4a0-4aaa-b928-fdeffc485412', 'isActive' => true, 'icon' => 'Icon123'],
            'favorites' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Favorites', 'description' => 'Favorites linked to the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '0671a345-1b8a-4cde-b525-fbf06aae6859', 'isActive' => true, 'icon' => 'IconHeart'],
            'linkedinBio' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'LinkedIn Bio', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '0e863565-1af7-4175-a4e1-d23d4ea54ba0', 'isActive' => false, 'icon' => 'IconBrandLinkedin'],
            'searchVector' => ['type' => FieldType::TS_VECTOR, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Search vector', 'description' => 'Field used for full-text search', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '2a4cb7b5-bbf1-4a26-94e8-7616e3124528', 'isActive' => true, 'icon' => 'IconUser'],
            'name' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Name', 'description' => 'The company name', 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '3c6bdce3-693b-4b77-ac04-aa6f81c47241', 'isActive' => true, 'icon' => 'IconBuildingSkyscraper'],
            'position' => ['type' => FieldType::POSITION, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Position', 'description' => 'Company record position', 'defaultValue' => 0, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '3c6d547d-a58d-4a06-b3da-45f406aed00c', 'isActive' => true, 'icon' => 'IconHierarchy2'],
            'createdBy' => ['type' => FieldType::ACTOR, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Created by', 'description' => 'The creator of the record', 'defaultValue' => array (
          'name' => '\'System\'',
          'source' => '\'MANUAL\'',
          'workspaceMemberId' => NULL,
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '3ca8814e-ed4d-4a5e-899c-0f8b1c63753c', 'isActive' => true, 'icon' => 'IconCreativeCommonsSa'],
            'timezone' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Timezone', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '3ff4481c-d854-4974-9767-3f1b2e78b97b', 'isActive' => false, 'icon' => 'IconWorld'],
            'isPublic' => ['type' => FieldType::BOOLEAN, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Is Public', 'description' => null, 'defaultValue' => false, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '5794778f-7c18-4005-bbfd-4b1f2ab5ebf2', 'isActive' => false, 'icon' => 'IconToggleLeft'],
            'opportunities' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Opportunities', 'description' => 'Opportunities linked to the company.', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '5a1bd295-9066-4654-bac7-f6244269f764', 'isActive' => true, 'icon' => 'IconTargetArrow'],
            'accountOwner' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Account Owner', 'description' => 'Your team member responsible for managing the company account', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '5b08b956-229e-40d6-bea6-62a78096f105', 'isActive' => true, 'icon' => 'IconUserCircle'],
            'timelineActivities' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Timeline Activities', 'description' => 'Timeline Activities linked to the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '6120ffaf-4c0d-46da-b69e-36f03fc79948', 'isActive' => true, 'icon' => 'IconIconTimelineEvent'],
            'phones' => ['type' => FieldType::PHONES, 'nullable' => true, 'hasHandler' => true, 'isCustom' => true, 'isSystem' => false, 'label' => 'Phones', 'description' => null, 'defaultValue' => array (
          'additionalPhones' => NULL,
          'primaryPhoneNumber' => '\'\'',
          'primaryPhoneCallingCode' => '\'\'',
          'primaryPhoneCountryCode' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '66bb22a5-295f-4b7e-a743-5450db6523ca', 'isActive' => true, 'icon' => 'IconPhone'],
            'facebook' => ['type' => FieldType::LINKS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => true, 'isSystem' => false, 'label' => 'Facebook', 'description' => null, 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => '\'[]\'',
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '6756874f-01d0-42f8-a43c-f8cba2976d4e', 'isActive' => true, 'icon' => 'IconBrandFacebook'],
            'linkedinLink' => ['type' => FieldType::LINKS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Linkedin', 'description' => 'The company Linkedin account', 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => NULL,
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '8e77673e-fb7b-40c4-8ac4-d49f8fce7c11', 'isActive' => true, 'icon' => 'IconBrandLinkedin'],
            'taskTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Tasks', 'description' => 'Tasks tied to the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => '98549bd0-fd06-47f9-b6c4-2272822c34d4', 'isActive' => true, 'icon' => 'IconCheckbox'],
            'noteTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Notes', 'description' => 'Notes tied to the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'a55fe448-ecad-4f17-8600-2f12fac37e21', 'isActive' => true, 'icon' => 'IconNotes'],
            'xLink' => ['type' => FieldType::LINKS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'X', 'description' => 'The company Twitter/X account', 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => NULL,
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'a7e9ae71-5a50-4ef9-b04f-7af743b17f14', 'isActive' => true, 'icon' => 'IconBrandX'],
            'address' => ['type' => FieldType::ADDRESS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Address', 'description' => 'Address of the company', 'defaultValue' => array (
          'addressLat' => NULL,
          'addressLng' => NULL,
          'addressCity' => '\'\'',
          'addressState' => '\'\'',
          'addressCountry' => '\'\'',
          'addressStreet1' => '\'\'',
          'addressStreet2' => '\'\'',
          'addressPostcode' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'acf470a0-e1b2-472e-97ad-23c4f7605173', 'isActive' => true, 'icon' => 'IconMap'],
            'annualRecurringRevenue' => ['type' => FieldType::CURRENCY, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'ARR', 'description' => 'Annual Recurring Revenue: The actual or estimated annual revenue of the company', 'defaultValue' => array (
          'amountMicros' => NULL,
          'currencyCode' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'b6da9c87-f56e-41d2-b60b-9acb1e23b596', 'isActive' => false, 'icon' => 'IconMoneybag'],
            'hubspotId' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'HubSpot Id', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'bcb93e0a-f2e9-49cd-9832-3212ce29039a', 'isActive' => true, 'icon' => 'Icon123'],
            'industry' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Industry', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'ce01f18e-7ca3-4846-bf6b-859c19d0f2c9', 'isActive' => true, 'icon' => 'IconTypography'],
            'yearFounded' => ['type' => FieldType::NUMBER, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Year Founded', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'd7d53d95-00a9-424e-9dcf-a8afed4b4b36', 'isActive' => true, 'icon' => 'IconNumber9'],
            'updatedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Last update', 'description' => 'Last time the record was changed', 'defaultValue' => 'now', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'd8529865-5ba1-4506-9e5f-6329c04c3665', 'isActive' => true, 'icon' => 'IconCalendarClock'],
            'lastActivityDate' => ['type' => FieldType::DATE_TIME, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Last Activity Date', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'dc287ee3-d08a-427a-84a0-5c8d7a08325a', 'isActive' => false, 'icon' => 'IconCalendarShare'],
            'createdAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Creation date', 'description' => 'Creation date', 'defaultValue' => 'now', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'e2ee73c8-b059-4203-9639-c6432f0835b4', 'isActive' => true, 'icon' => 'IconCalendar'],
            'employees' => ['type' => FieldType::NUMBER, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Employees', 'description' => 'Number of employees in the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'e36dbbcf-26ee-43d9-8a1e-1658ee337526', 'isActive' => true, 'icon' => 'IconUsers'],
            'annualRevenue' => ['type' => FieldType::CURRENCY, 'nullable' => true, 'hasHandler' => true, 'isCustom' => true, 'isSystem' => false, 'label' => 'Annual Revenue', 'description' => null, 'defaultValue' => array (
          'amountMicros' => NULL,
          'currencyCode' => '\'USD\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'ec00c04b-545d-4094-bdf9-28775b7be7bd', 'isActive' => false, 'icon' => 'IconMoneybag'],
            'deletedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Deleted at', 'description' => 'Date when the record was deleted', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'ef3bc449-36cf-4337-b862-d234a5b8e3df', 'isActive' => true, 'icon' => 'IconCalendarMinus'],
            'description' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Description', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'f1a9a3a6-2dc3-4661-913c-684c547acbbe', 'isActive' => false, 'icon' => 'IconTextPlus'],
            'idealCustomerProfile' => ['type' => FieldType::BOOLEAN, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'ICP', 'description' => 'Ideal Customer Profile:  Indicates whether the company is the most suitable and valuable customer for you', 'defaultValue' => false, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'f423f008-ba22-4e53-8060-69a652d70cea', 'isActive' => false, 'icon' => 'IconTarget'],
            'domainName' => ['type' => FieldType::LINKS, 'nullable' => false, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Domain Name', 'description' => 'The company website URL. We use this url to fetch the company icon', 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => NULL,
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'f5a95c5b-4aea-45a8-bf67-2379b5362f75', 'isActive' => true, 'icon' => 'IconLink'],
            'people' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'People', 'description' => 'People linked to the company.', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'f6d162db-5c3b-4e9f-86ca-9c915ed09ae4', 'isActive' => true, 'icon' => 'IconUsers'],
            'attachments' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Attachments', 'description' => 'Attachments linked to the company', 'defaultValue' => null, 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'fa941180-3343-4afe-98b1-dae493ae3327', 'isActive' => true, 'icon' => 'IconFileImport'],
            'lifecycleStage' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Lifecycle stage', 'description' => null, 'defaultValue' => '\'LEAD\'', 'objectMetadataId' => 'a65e1101-f0d1-4bb9-8e25-30376cb7bf44', 'id' => 'fbff1430-3c3e-46e5-9319-b25daa1179b5', 'isActive' => false, 'icon' => 'IconTag'],
            default => null,
        };
    }

    protected static function getFieldToApiMap(): array
    {
        return [
            'favorites' => 'favoritesId',
            'opportunities' => 'opportunitiesId',
            'accountOwner' => 'accountOwnerId',
            'timelineActivities' => 'timelineActivitiesId',
            'taskTargets' => 'taskTargetsId',
            'noteTargets' => 'noteTargetsId',
            'people' => 'peopleId',
            'attachments' => 'attachmentsId',
        ];
    }

    protected static function getApiToFieldMap(): array
    {
        return [
            'favoritesId' => 'favorites',
            'opportunitiesId' => 'opportunities',
            'accountOwnerId' => 'accountOwner',
            'timelineActivitiesId' => 'timelineActivities',
            'taskTargetsId' => 'taskTargets',
            'noteTargetsId' => 'noteTargets',
            'peopleId' => 'people',
            'attachmentsId' => 'attachments',
        ];
    }

    /**
     * Get originalTrafficSource.
     */
    public function getOriginalTrafficSource(): ?string
    {
        return $this->get('originalTrafficSource');
    }

    /**
     * Set originalTrafficSource.
     */
    public function setOriginalTrafficSource(?string $value): self
    {
        $this->set('originalTrafficSource', $value);
        return $this;
    }

    /**
     * Get id.
     */
    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * Get favorites.
     */
    public function getFavorites(): ?string
    {
        return $this->get('favorites');
    }

    /**
     * Get linkedinBio.
     */
    public function getLinkedinBio(): string
    {
        return $this->get('linkedinBio');
    }

    /**
     * Set linkedinBio.
     */
    public function setLinkedinBio(string $value): self
    {
        $this->set('linkedinBio', $value);
        return $this;
    }

    /**
     * Get searchVector.
     */
    public function getSearchVector(): mixed
    {
        return $this->get('searchVector');
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
     * Get position.
     */
    public function getPosition(): mixed
    {
        return $this->get('position');
    }

    /**
     * Get createdBy.
     */
    public function getCreatedBy(): mixed
    {
        return $this->get('createdBy');
    }

    /**
     * Get timezone.
     */
    public function getTimezone(): string
    {
        return $this->get('timezone');
    }

    /**
     * Set timezone.
     */
    public function setTimezone(string $value): self
    {
        $this->set('timezone', $value);
        return $this;
    }

    /**
     * Get isPublic.
     */
    public function getIsPublic(): ?bool
    {
        return $this->get('isPublic');
    }

    /**
     * Set isPublic.
     */
    public function setIsPublic(?bool $value): self
    {
        $this->set('isPublic', $value);
        return $this;
    }

    /**
     * Get opportunities.
     */
    public function getOpportunities(): ?string
    {
        return $this->get('opportunities');
    }

    /**
     * Set opportunities.
     */
    public function setOpportunities(?string $value): self
    {
        $this->set('opportunities', $value);
        return $this;
    }

    /**
     * Get accountOwner.
     */
    public function getAccountOwner(): ?string
    {
        return $this->get('accountOwner');
    }

    /**
     * Set accountOwner.
     */
    public function setAccountOwner(?string $value): self
    {
        $this->set('accountOwner', $value);
        return $this;
    }

    /**
     * Get timelineActivities.
     */
    public function getTimelineActivities(): ?string
    {
        return $this->get('timelineActivities');
    }

    /**
     * Get phones.
     */
    public function getPhones(): ?PhoneCollection
    {
        return $this->get('phones');
    }

    /**
     * Set phones.
     */
    public function setPhones(?PhoneCollection $value): self
    {
        $this->set('phones', $value);
        return $this;
    }

    /**
     * Get facebook.
     */
    public function getFacebook(): ?LinkCollection
    {
        return $this->get('facebook');
    }

    /**
     * Set facebook.
     */
    public function setFacebook(?LinkCollection $value): self
    {
        $this->set('facebook', $value);
        return $this;
    }

    /**
     * Get linkedinLink.
     */
    public function getLinkedinLink(): ?LinkCollection
    {
        return $this->get('linkedinLink');
    }

    /**
     * Set linkedinLink.
     */
    public function setLinkedinLink(?LinkCollection $value): self
    {
        $this->set('linkedinLink', $value);
        return $this;
    }

    /**
     * Get taskTargets.
     */
    public function getTaskTargets(): ?string
    {
        return $this->get('taskTargets');
    }

    /**
     * Set taskTargets.
     */
    public function setTaskTargets(?string $value): self
    {
        $this->set('taskTargets', $value);
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
     * Set noteTargets.
     */
    public function setNoteTargets(?string $value): self
    {
        $this->set('noteTargets', $value);
        return $this;
    }

    /**
     * Get xLink.
     */
    public function getXLink(): ?LinkCollection
    {
        return $this->get('xLink');
    }

    /**
     * Set xLink.
     */
    public function setXLink(?LinkCollection $value): self
    {
        $this->set('xLink', $value);
        return $this;
    }

    /**
     * Get address.
     */
    public function getAddress(): ?Address
    {
        return $this->get('address');
    }

    /**
     * Set address.
     */
    public function setAddress(?Address $value): self
    {
        $this->set('address', $value);
        return $this;
    }

    /**
     * Get annualRecurringRevenue.
     */
    public function getAnnualRecurringRevenue(): ?Currency
    {
        return $this->get('annualRecurringRevenue');
    }

    /**
     * Set annualRecurringRevenue.
     */
    public function setAnnualRecurringRevenue(?Currency $value): self
    {
        $this->set('annualRecurringRevenue', $value);
        return $this;
    }

    /**
     * Get hubspotId.
     */
    public function getHubspotId(): string
    {
        return $this->get('hubspotId');
    }

    /**
     * Set hubspotId.
     */
    public function setHubspotId(string $value): self
    {
        $this->set('hubspotId', $value);
        return $this;
    }

    /**
     * Get industry.
     */
    public function getIndustry(): string
    {
        return $this->get('industry');
    }

    /**
     * Set industry.
     */
    public function setIndustry(string $value): self
    {
        $this->set('industry', $value);
        return $this;
    }

    /**
     * Get yearFounded.
     */
    public function getYearFounded(): ?int
    {
        return $this->get('yearFounded');
    }

    /**
     * Set yearFounded.
     */
    public function setYearFounded(?int $value): self
    {
        $this->set('yearFounded', $value);
        return $this;
    }

    /**
     * Get updatedAt.
     */
    public function getUpdatedAt(): string
    {
        return $this->get('updatedAt');
    }

    /**
     * Get lastActivityDate.
     */
    public function getLastActivityDate(): ?string
    {
        return $this->get('lastActivityDate');
    }

    /**
     * Set lastActivityDate.
     */
    public function setLastActivityDate(?string $value): self
    {
        $this->set('lastActivityDate', $value);
        return $this;
    }

    /**
     * Get createdAt.
     */
    public function getCreatedAt(): string
    {
        return $this->get('createdAt');
    }

    /**
     * Get employees.
     */
    public function getEmployees(): ?int
    {
        return $this->get('employees');
    }

    /**
     * Set employees.
     */
    public function setEmployees(?int $value): self
    {
        $this->set('employees', $value);
        return $this;
    }

    /**
     * Get annualRevenue.
     */
    public function getAnnualRevenue(): ?Currency
    {
        return $this->get('annualRevenue');
    }

    /**
     * Set annualRevenue.
     */
    public function setAnnualRevenue(?Currency $value): self
    {
        $this->set('annualRevenue', $value);
        return $this;
    }

    /**
     * Get deletedAt.
     */
    public function getDeletedAt(): ?string
    {
        return $this->get('deletedAt');
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->get('description');
    }

    /**
     * Set description.
     */
    public function setDescription(string $value): self
    {
        $this->set('description', $value);
        return $this;
    }

    /**
     * Get idealCustomerProfile.
     */
    public function getIdealCustomerProfile(): bool
    {
        return $this->get('idealCustomerProfile');
    }

    /**
     * Set idealCustomerProfile.
     */
    public function setIdealCustomerProfile(bool $value): self
    {
        $this->set('idealCustomerProfile', $value);
        return $this;
    }

    /**
     * Get domainName.
     */
    public function getDomainName(): LinkCollection
    {
        return $this->get('domainName');
    }

    /**
     * Set domainName.
     */
    public function setDomainName(LinkCollection $value): self
    {
        $this->set('domainName', $value);
        return $this;
    }

    /**
     * Get people.
     */
    public function getPeople(): ?string
    {
        return $this->get('people');
    }

    /**
     * Set people.
     */
    public function setPeople(?string $value): self
    {
        $this->set('people', $value);
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
     * Get lifecycleStage.
     */
    public function getLifecycleStage(): ?string
    {
        return $this->get('lifecycleStage');
    }

    /**
     * Set lifecycleStage.
     */
    public function setLifecycleStage(?string $value): self
    {
        $this->set('lifecycleStage', $value);
        return $this;
    }
}
