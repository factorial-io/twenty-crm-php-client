<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\Collection\EmailCollection;
use Factorial\TwentyCrm\Collection\LinkCollection;
use Factorial\TwentyCrm\Collection\PhoneCollection;
use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\Enums\FieldType;

/**
 * Person entity (auto-generated).
 *
 * This class provides typed access to person entity fields.
 * Generated from Twenty CRM metadata.
 *
 * All metadata is baked into this class at generation time,
 * so no runtime API calls are needed.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Person extends StaticEntity
{
    protected static function getEntityName(): string
    {
        return 'person';
    }

    protected static function getEntityNamePlural(): string
    {
        return 'people';
    }

    protected static function getApiEndpoint(): string
    {
        return '/people';
    }

    protected static function getAllFieldNames(): array
    {
        return [
            'leadStatus',
            'noteTargets',
            'emails',
            'pointOfContactForOpportunities',
            'numberOfTimesContacted',
            'createdAt',
            'name',
            'attachments',
            'messageParticipants',
            'xLink',
            'contactAddress',
            'seniority',
            'createdBy',
            'owner',
            'industry',
            'outreach',
            'hubspotId',
            'calendarEventParticipants',
            'timelineActivities',
            'jobTitle',
            'position',
            'company',
            'mobilePhones',
            'recordSource',
            'city',
            'originalTrafficSource',
            'town',
            'phones',
            'taskTargets',
            'deletedAt',
            'avatarUrl',
            'updatedAt',
            'campaignLink',
            'campaignTmp',
            'leadSource',
            'country',
            'id',
            'lifecycleStage',
            'favorites',
            'campaign',
            'linkedinLink',
            'lastActivityDate',
            'searchVector',
        ];
    }

    protected static function getFieldMetadata(string $fieldName): ?array
    {
        return match ($fieldName) {
            'leadStatus' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Lead Status', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '060d4b7d-bf1a-430e-82a1-7ef6d04751ac', 'isActive' => true, 'icon' => 'IconTag'],
            'noteTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Notes', 'description' => 'Notes tied to the contact', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '0650ce8f-1a87-48eb-ae2f-0859956d3e56', 'isActive' => true, 'icon' => 'IconNotes'],
            'emails' => ['type' => FieldType::EMAILS, 'nullable' => false, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Emails', 'description' => 'Contact’s Emails', 'defaultValue' => array (
          'primaryEmail' => '\'\'',
          'additionalEmails' => NULL,
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '1011d51d-6cea-44cf-b9c6-0075d47ac296', 'isActive' => true, 'icon' => 'IconMail'],
            'pointOfContactForOpportunities' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Opportunities', 'description' => 'List of opportunities for which that person is the point of contact', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '10e73241-6110-4b9f-92a5-2787eed5c2d3', 'isActive' => true, 'icon' => 'IconTargetArrow'],
            'numberOfTimesContacted' => ['type' => FieldType::NUMBER, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Number of times contacted', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '14a1501c-2f4a-4b45-a16f-cfe2f509151e', 'isActive' => false, 'icon' => 'IconNumber9'],
            'createdAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Creation date', 'description' => 'Creation date', 'defaultValue' => 'now', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '162b96ee-33cb-45af-8fd1-add1f57d578f', 'isActive' => true, 'icon' => 'IconCalendar'],
            'name' => ['type' => FieldType::FULL_NAME, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Name', 'description' => 'Contact’s name', 'defaultValue' => array (
          'lastName' => '\'\'',
          'firstName' => '\'\'',
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '17f8cb3a-50d2-42cf-9da8-e249efbe9f7a', 'isActive' => true, 'icon' => 'IconUser'],
            'attachments' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Attachments', 'description' => 'Attachments linked to the contact.', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '22a13828-3154-44c9-a6c1-e930a25c647b', 'isActive' => true, 'icon' => 'IconFileImport'],
            'messageParticipants' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Message Participants', 'description' => 'Message Participants', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '259e2334-ff53-486e-9303-d53b9c81179d', 'isActive' => true, 'icon' => 'IconUserCircle'],
            'xLink' => ['type' => FieldType::LINKS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'X', 'description' => 'Contact’s X/Twitter account', 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => NULL,
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '25adc432-1a10-4f82-ae68-01c1478bb171', 'isActive' => false, 'icon' => 'IconBrandX'],
            'contactAddress' => ['type' => FieldType::ADDRESS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => true, 'isSystem' => false, 'label' => 'Contact Address', 'description' => null, 'defaultValue' => array (
          'addressLat' => NULL,
          'addressLng' => NULL,
          'addressCity' => NULL,
          'addressState' => NULL,
          'addressCountry' => NULL,
          'addressStreet1' => '\'\'',
          'addressStreet2' => NULL,
          'addressPostcode' => NULL,
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '30899133-1634-4510-9ea6-b389b9750118', 'isActive' => false, 'icon' => 'IconMap'],
            'seniority' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Seniority', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '36a13db4-6ee7-4e67-9a2c-4b8f91abef89', 'isActive' => false, 'icon' => 'IconTag'],
            'createdBy' => ['type' => FieldType::ACTOR, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Created by', 'description' => 'The creator of the record', 'defaultValue' => array (
          'name' => '\'System\'',
          'source' => '\'MANUAL\'',
          'workspaceMemberId' => NULL,
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '436e8f92-7380-4a1a-8581-ee916ad685ed', 'isActive' => true, 'icon' => 'IconCreativeCommonsSa'],
            'owner' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Owner', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '43a47d6d-2869-4b83-bb21-367f543c11c9', 'isActive' => true, 'icon' => 'IconUserCircle'],
            'industry' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Industry', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '48c6bfc2-7dde-405f-9c81-363145d0d2c6', 'isActive' => false, 'icon' => 'IconTypography'],
            'outreach' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Outreach', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '4d3e8bbc-0bc2-47db-b9f6-c3380e9bb329', 'isActive' => true, 'icon' => 'IconAdCircle'],
            'hubspotId' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'HubSpot Id', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '66a0b998-040f-4687-8929-d2f1443a5181', 'isActive' => true, 'icon' => 'IconTypography'],
            'calendarEventParticipants' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Calendar Event Participants', 'description' => 'Calendar Event Participants', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '6e20ea4f-ca9a-457a-a3dd-41fba5fbed04', 'isActive' => true, 'icon' => 'IconCalendar'],
            'timelineActivities' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Events', 'description' => 'Events linked to the person', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '706d70fd-b52a-4eb9-876d-5b1da5a7053f', 'isActive' => true, 'icon' => 'IconTimelineEvent'],
            'jobTitle' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Job Title', 'description' => 'Contact’s job title', 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '775548e5-062f-49a7-8ced-44b48007368d', 'isActive' => true, 'icon' => 'IconBriefcase'],
            'position' => ['type' => FieldType::POSITION, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Position', 'description' => 'Person record Position', 'defaultValue' => 0, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '7ce5abc0-2f1c-435a-9ad9-591419d742ea', 'isActive' => true, 'icon' => 'IconHierarchy2'],
            'company' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Company', 'description' => 'Contact’s company', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '809f69bc-e504-4314-8a5b-90c4d541dcf2', 'isActive' => true, 'icon' => 'IconBuildingSkyscraper'],
            'mobilePhones' => ['type' => FieldType::PHONES, 'nullable' => true, 'hasHandler' => true, 'isCustom' => true, 'isSystem' => false, 'label' => 'Mobile Phones', 'description' => null, 'defaultValue' => array (
          'additionalPhones' => NULL,
          'primaryPhoneNumber' => '\'\'',
          'primaryPhoneCallingCode' => '\'\'',
          'primaryPhoneCountryCode' => '\'\'',
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '842da0d2-192c-4ccc-9be2-fbd7936f07f7', 'isActive' => false, 'icon' => 'IconPhone'],
            'recordSource' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Record Source', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '8c895cec-db21-4368-9c9c-19587bd67aa0', 'isActive' => true, 'icon' => 'IconTag'],
            'city' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'City', 'description' => 'Contact’s city', 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '8c9c4f90-52d3-4b15-b23b-73c535f45e6b', 'isActive' => false, 'icon' => 'IconMap'],
            'originalTrafficSource' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Original Traffic Source', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '953ae9c0-f84a-4064-b814-ce1a943474fa', 'isActive' => false, 'icon' => 'IconTag'],
            'town' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Town', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => '981341ef-05de-47b7-9535-a85ab5019ebb', 'isActive' => true, 'icon' => 'IconTypography'],
            'phones' => ['type' => FieldType::PHONES, 'nullable' => false, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Phones', 'description' => 'Contact’s phone numbers', 'defaultValue' => array (
          'additionalPhones' => NULL,
          'primaryPhoneNumber' => '\'\'',
          'primaryPhoneCallingCode' => '\'\'',
          'primaryPhoneCountryCode' => '\'\'',
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'a4008e67-13d8-49af-a849-a5cc23c3a117', 'isActive' => true, 'icon' => 'IconPhone'],
            'taskTargets' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Tasks', 'description' => 'Tasks tied to the contact', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'a657b635-6ba8-4042-bf5f-9c87a631edd4', 'isActive' => true, 'icon' => 'IconCheckbox'],
            'deletedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Deleted at', 'description' => 'Date when the record was deleted', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'aefffe32-c77a-4a4e-9789-7e472968fa78', 'isActive' => true, 'icon' => 'IconCalendarMinus'],
            'avatarUrl' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Avatar', 'description' => 'Contact’s avatar', 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'b0f59552-cacd-4a11-8471-a0edbb8de1af', 'isActive' => true, 'icon' => 'IconFileUpload'],
            'updatedAt' => ['type' => FieldType::DATE_TIME, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => false, 'label' => 'Last update', 'description' => 'Last time the record was changed', 'defaultValue' => 'now', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'b4e0e5b8-ec5c-406e-8f5d-97bbba6ece3e', 'isActive' => true, 'icon' => 'IconCalendarClock'],
            'campaignLink' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Campaign Link', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'ca410bad-88d3-4eb7-96f8-87690692ba7d', 'isActive' => false, 'icon' => 'IconRelationOneToMany'],
            'campaignTmp' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Campaign (tmp)', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'ce6b0e14-f8ed-44b2-ad51-25cd576b076f', 'isActive' => true, 'icon' => 'IconTag'],
            'leadSource' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Lead Source', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'dadcb6ae-7bb1-4a3f-b3b7-d74d5a8c6a96', 'isActive' => true, 'icon' => 'IconTag'],
            'country' => ['type' => FieldType::TEXT, 'nullable' => false, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Country', 'description' => null, 'defaultValue' => '\'\'', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'db891677-1ca2-4f16-a437-f46da8a021b1', 'isActive' => true, 'icon' => 'IconTypography'],
            'id' => ['type' => FieldType::UUID, 'nullable' => false, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Id', 'description' => 'Id', 'defaultValue' => 'uuid', 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'e146a95a-2e25-4a1b-bced-e273c3a70567', 'isActive' => true, 'icon' => 'Icon123'],
            'lifecycleStage' => ['type' => FieldType::SELECT, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Lifecycle Stage', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'e1d52439-0849-46e5-887e-0d2bf69463aa', 'isActive' => true, 'icon' => 'IconTag'],
            'favorites' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Favorites', 'description' => 'Favorites linked to the contact', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'e91e753b-34f3-41df-8eb3-e7514dc2edd2', 'isActive' => true, 'icon' => 'IconHeart'],
            'campaign' => ['type' => FieldType::RELATION, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Campaign', 'description' => 'Assign all campaigns the user should be targeted in', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'f1dbce8e-64a0-4928-b8eb-471936d30708', 'isActive' => false, 'icon' => 'IconRelationOneToMany'],
            'linkedinLink' => ['type' => FieldType::LINKS, 'nullable' => true, 'hasHandler' => true, 'isCustom' => false, 'isSystem' => false, 'label' => 'Linkedin', 'description' => 'Contact’s Linkedin account', 'defaultValue' => array (
          'primaryLinkUrl' => '\'\'',
          'secondaryLinks' => NULL,
          'primaryLinkLabel' => '\'\'',
        ), 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'fdbe0a74-5b7c-449e-b782-c101c6d36b25', 'isActive' => true, 'icon' => 'IconBrandLinkedin'],
            'lastActivityDate' => ['type' => FieldType::DATE_TIME, 'nullable' => true, 'hasHandler' => false, 'isCustom' => true, 'isSystem' => false, 'label' => 'Last Activity Date', 'description' => null, 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'fe0c43e8-af42-4292-9a4f-284462fe14c3', 'isActive' => false, 'icon' => 'IconCalendarShare'],
            'searchVector' => ['type' => FieldType::TS_VECTOR, 'nullable' => true, 'hasHandler' => false, 'isCustom' => false, 'isSystem' => true, 'label' => 'Search vector', 'description' => 'Field used for full-text search', 'defaultValue' => null, 'objectMetadataId' => 'db3f5ce4-76d7-4832-a9f9-cc9729b42ef1', 'id' => 'ff574790-cfcf-47f6-93c7-88b82efed2b0', 'isActive' => true, 'icon' => 'IconUser'],
            default => null,
        };
    }

    protected static function getFieldToApiMap(): array
    {
        return [
            'noteTargets' => 'noteTargetsId',
            'pointOfContactForOpportunities' => 'pointOfContactForOpportunitiesId',
            'attachments' => 'attachmentsId',
            'messageParticipants' => 'messageParticipantsId',
            'owner' => 'ownerId',
            'outreach' => 'outreachId',
            'calendarEventParticipants' => 'calendarEventParticipantsId',
            'timelineActivities' => 'timelineActivitiesId',
            'company' => 'companyId',
            'taskTargets' => 'taskTargetsId',
            'campaignLink' => 'campaignLinkId',
            'favorites' => 'favoritesId',
            'campaign' => 'campaignId',
        ];
    }

    protected static function getApiToFieldMap(): array
    {
        return [
            'noteTargetsId' => 'noteTargets',
            'pointOfContactForOpportunitiesId' => 'pointOfContactForOpportunities',
            'attachmentsId' => 'attachments',
            'messageParticipantsId' => 'messageParticipants',
            'ownerId' => 'owner',
            'outreachId' => 'outreach',
            'calendarEventParticipantsId' => 'calendarEventParticipants',
            'timelineActivitiesId' => 'timelineActivities',
            'companyId' => 'company',
            'taskTargetsId' => 'taskTargets',
            'campaignLinkId' => 'campaignLink',
            'favoritesId' => 'favorites',
            'campaignId' => 'campaign',
        ];
    }

    /**
     * Get leadStatus.
     */
    public function getLeadStatus(): ?string
    {
        return $this->get('leadStatus');
    }

    /**
     * Set leadStatus.
     */
    public function setLeadStatus(?string $value): self
    {
        $this->set('leadStatus', $value);
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
     * Get emails.
     */
    public function getEmails(): EmailCollection
    {
        return $this->get('emails');
    }

    /**
     * Set emails.
     */
    public function setEmails(EmailCollection $value): self
    {
        $this->set('emails', $value);
        return $this;
    }

    /**
     * Get pointOfContactForOpportunities.
     */
    public function getPointOfContactForOpportunities(): ?string
    {
        return $this->get('pointOfContactForOpportunities');
    }

    /**
     * Set pointOfContactForOpportunities.
     */
    public function setPointOfContactForOpportunities(?string $value): self
    {
        $this->set('pointOfContactForOpportunities', $value);
        return $this;
    }

    /**
     * Get numberOfTimesContacted.
     */
    public function getNumberOfTimesContacted(): ?int
    {
        return $this->get('numberOfTimesContacted');
    }

    /**
     * Set numberOfTimesContacted.
     */
    public function setNumberOfTimesContacted(?int $value): self
    {
        $this->set('numberOfTimesContacted', $value);
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
     * Get name.
     */
    public function getName(): ?Name
    {
        return $this->get('name');
    }

    /**
     * Set name.
     */
    public function setName(?Name $value): self
    {
        $this->set('name', $value);
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
     * Get messageParticipants.
     */
    public function getMessageParticipants(): ?string
    {
        return $this->get('messageParticipants');
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
     * Get contactAddress.
     */
    public function getContactAddress(): ?Address
    {
        return $this->get('contactAddress');
    }

    /**
     * Set contactAddress.
     */
    public function setContactAddress(?Address $value): self
    {
        $this->set('contactAddress', $value);
        return $this;
    }

    /**
     * Get seniority.
     */
    public function getSeniority(): ?string
    {
        return $this->get('seniority');
    }

    /**
     * Set seniority.
     */
    public function setSeniority(?string $value): self
    {
        $this->set('seniority', $value);
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
     * Get owner.
     */
    public function getOwner(): ?string
    {
        return $this->get('owner');
    }

    /**
     * Set owner.
     */
    public function setOwner(?string $value): self
    {
        $this->set('owner', $value);
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
     * Get outreach.
     */
    public function getOutreach(): ?string
    {
        return $this->get('outreach');
    }

    /**
     * Set outreach.
     */
    public function setOutreach(?string $value): self
    {
        $this->set('outreach', $value);
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
     * Get calendarEventParticipants.
     */
    public function getCalendarEventParticipants(): ?string
    {
        return $this->get('calendarEventParticipants');
    }

    /**
     * Get timelineActivities.
     */
    public function getTimelineActivities(): ?string
    {
        return $this->get('timelineActivities');
    }

    /**
     * Get jobTitle.
     */
    public function getJobTitle(): string
    {
        return $this->get('jobTitle');
    }

    /**
     * Set jobTitle.
     */
    public function setJobTitle(string $value): self
    {
        $this->set('jobTitle', $value);
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
     * Get company.
     */
    public function getCompany(): ?string
    {
        return $this->get('company');
    }

    /**
     * Set company.
     */
    public function setCompany(?string $value): self
    {
        $this->set('company', $value);
        return $this;
    }

    /**
     * Get mobilePhones.
     */
    public function getMobilePhones(): ?PhoneCollection
    {
        return $this->get('mobilePhones');
    }

    /**
     * Set mobilePhones.
     */
    public function setMobilePhones(?PhoneCollection $value): self
    {
        $this->set('mobilePhones', $value);
        return $this;
    }

    /**
     * Get recordSource.
     */
    public function getRecordSource(): ?string
    {
        return $this->get('recordSource');
    }

    /**
     * Set recordSource.
     */
    public function setRecordSource(?string $value): self
    {
        $this->set('recordSource', $value);
        return $this;
    }

    /**
     * Get city.
     */
    public function getCity(): string
    {
        return $this->get('city');
    }

    /**
     * Set city.
     */
    public function setCity(string $value): self
    {
        $this->set('city', $value);
        return $this;
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
     * Get town.
     */
    public function getTown(): string
    {
        return $this->get('town');
    }

    /**
     * Set town.
     */
    public function setTown(string $value): self
    {
        $this->set('town', $value);
        return $this;
    }

    /**
     * Get phones.
     */
    public function getPhones(): PhoneCollection
    {
        return $this->get('phones');
    }

    /**
     * Set phones.
     */
    public function setPhones(PhoneCollection $value): self
    {
        $this->set('phones', $value);
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
     * Get deletedAt.
     */
    public function getDeletedAt(): ?string
    {
        return $this->get('deletedAt');
    }

    /**
     * Get avatarUrl.
     */
    public function getAvatarUrl(): string
    {
        return $this->get('avatarUrl');
    }

    /**
     * Get updatedAt.
     */
    public function getUpdatedAt(): string
    {
        return $this->get('updatedAt');
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
     * Get campaignTmp.
     */
    public function getCampaignTmp(): ?string
    {
        return $this->get('campaignTmp');
    }

    /**
     * Set campaignTmp.
     */
    public function setCampaignTmp(?string $value): self
    {
        $this->set('campaignTmp', $value);
        return $this;
    }

    /**
     * Get leadSource.
     */
    public function getLeadSource(): ?string
    {
        return $this->get('leadSource');
    }

    /**
     * Set leadSource.
     */
    public function setLeadSource(?string $value): self
    {
        $this->set('leadSource', $value);
        return $this;
    }

    /**
     * Get country.
     */
    public function getCountry(): string
    {
        return $this->get('country');
    }

    /**
     * Set country.
     */
    public function setCountry(string $value): self
    {
        $this->set('country', $value);
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

    /**
     * Get favorites.
     */
    public function getFavorites(): ?string
    {
        return $this->get('favorites');
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
     * Get searchVector.
     */
    public function getSearchVector(): mixed
    {
        return $this->get('searchVector');
    }
}
