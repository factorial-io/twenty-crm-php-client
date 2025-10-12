<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\Collection\EmailCollection;
use Factorial\TwentyCrm\Collection\LinkCollection;
use Factorial\TwentyCrm\Collection\PhoneCollection;
use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * Person entity (auto-generated).
 *
 * This class provides typed access to person entity fields.
 * Generated from Twenty CRM metadata.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Person extends DynamicEntity
{
    public function __construct(EntityDefinition $definition, array $data = [])
    {
        parent::__construct($definition, $data);
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
