<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Entity;

use Factorial\TwentyCrm\DTO\Address;
use Factorial\TwentyCrm\DTO\Currency;
use Factorial\TwentyCrm\DTO\DynamicEntity;
use Factorial\TwentyCrm\DTO\LinkCollection;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\Metadata\EntityDefinition;

/**
 * Company entity (auto-generated).
 *
 * This class provides typed access to company entity fields.
 * Generated from Twenty CRM metadata.
 *
 * @codingStandardsIgnoreFile
 * @phpstan-ignore-file
 */
class Company extends DynamicEntity
{
    public function __construct(EntityDefinition $definition, array $data = [])
    {
        parent::__construct($definition, $data);
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
    public function getFavorites(): mixed
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
    public function getOpportunities(): mixed
    {
        return $this->get('opportunities');
    }

    /**
     * Set opportunities.
     */
    public function setOpportunities(mixed $value): self
    {
        $this->set('opportunities', $value);
        return $this;
    }

    /**
     * Get accountOwner.
     */
    public function getAccountOwner(): mixed
    {
        return $this->get('accountOwner');
    }

    /**
     * Set accountOwner.
     */
    public function setAccountOwner(mixed $value): self
    {
        $this->set('accountOwner', $value);
        return $this;
    }

    /**
     * Get timelineActivities.
     */
    public function getTimelineActivities(): mixed
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
    public function getTaskTargets(): mixed
    {
        return $this->get('taskTargets');
    }

    /**
     * Set taskTargets.
     */
    public function setTaskTargets(mixed $value): self
    {
        $this->set('taskTargets', $value);
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
     * Set noteTargets.
     */
    public function setNoteTargets(mixed $value): self
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
    public function getPeople(): mixed
    {
        return $this->get('people');
    }

    /**
     * Set people.
     */
    public function setPeople(mixed $value): self
    {
        $this->set('people', $value);
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
