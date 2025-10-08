<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Contact data transfer object.
 */
class Contact
{
    /**
     * Contact constructor.
     *
     * @param string|null $id
     *   The contact ID.
     * @param string|null $email
     *   The contact email.
     * @param string|null $firstName
     *   The contact first name.
     * @param string|null $lastName
     *   The contact last name.
     * @param \Factorial\TwentyCrm\DTO\PhoneCollection|null $phones
     *   The contact phone collection.
     * @param \Factorial\TwentyCrm\DTO\PhoneCollection|null $mobilePhones
     *   The mobile phone collection.
     * @param \Factorial\TwentyCrm\DTO\LinkCollection|null $linkedInLink
     *   The LinkedIn link collection.
     * @param \Factorial\TwentyCrm\DTO\LinkCollection|null $xLink
     *   The X (Twitter) link collection.
     * @param string|null $jobTitle
     *   The contact job title.
     * @param string|null $companyId
     *   The associated company ID.
     * @param array $customFields
     *   Custom field values.
     * @param \DateTimeInterface|null $createdAt
     *   Creation timestamp.
     * @param \DateTimeInterface|null $updatedAt
     *   Last update timestamp.
     */
    public function __construct(
        private ?string $id = null,
        private ?string $email = null,
        private ?string $firstName = null,
        private ?string $lastName = null,
        private ?PhoneCollection $phones = null,
        private ?PhoneCollection $mobilePhones = null,
        private ?LinkCollection $linkedInLink = null,
        private ?LinkCollection $xLink = null,
        private ?string $jobTitle = null,
        private ?string $companyId = null,
        private array $customFields = [],
        private ?\DateTimeInterface $createdAt = null,
        private ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    /**
     * Create Contact from API response array.
     *
     * @param array $data
     *   The API response data.
     *
     * @return self
     *   The Contact instance.
     */
    public static function fromArray(array $data): self
    {
        $createdAt = isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null;
        $updatedAt = isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null;

        // Twenty CRM uses complex objects for emails, phones, names
        // Extract email from emails object (primaryEmail or first email)
        $email = null;
        if (isset($data['emails']['primaryEmail'])) {
            $email = $data['emails']['primaryEmail'];
        } elseif (isset($data['emails']['additionalEmails'][0])) {
            $email = $data['emails']['additionalEmails'][0];
        }

        // Extract phones collection
        $phones = null;
        if (isset($data['phones']) && is_array($data['phones'])) {
            $phones = PhoneCollection::fromArray($data['phones']);
        }

        // Extract mobile phones collection
        $mobilePhones = null;
        if (isset($data['mobilePhones']) && is_array($data['mobilePhones'])) {
            $mobilePhones = PhoneCollection::fromArray($data['mobilePhones']);
        }

        // Extract LinkedIn link collection
        $linkedInLink = null;
        if (isset($data['linkedinLink']) && is_array($data['linkedinLink'])) {
            $linkedInLink = LinkCollection::fromArray($data['linkedinLink']);
        }

        // Extract X (Twitter) link collection
        $xLink = null;
        if (isset($data['xLink']) && is_array($data['xLink'])) {
            $xLink = LinkCollection::fromArray($data['xLink']);
        }

        // Extract names from name object
        $firstName = $data['name']['firstName'] ?? null;
        $lastName = $data['name']['lastName'] ?? null;

        // Extract job title
        $jobTitle = $data['jobTitle'] ?? null;

        // Extract standard fields for Twenty CRM (including read-only fields)
        $standardFields = [
          'id', 'emails', 'phones', 'name', 'companyId', 'createdAt', 'updatedAt', 'deletedAt',
          'jobTitle', 'city', 'avatarUrl', 'position', 'createdBy', 'searchVector', 'lastActivityDate',
          'country', 'town', 'contactAddress', 'hubspotId', 'industry', 'mobilePhones',
          'numberOfTimesContacted', 'seniority', 'ownerId', 'leadSource', 'lifecycleStage',
          'originalTrafficSource', 'recordSource', 'leadStatus', 'campaignTmp', 'outreachId',
          'linkedinLink', 'xLink',
        ];
        $customFields = array_diff_key($data, array_flip($standardFields));

        return new self(
            id: $data['id'] ?? null,
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            phones: $phones,
            mobilePhones: $mobilePhones,
            linkedInLink: $linkedInLink,
            xLink: $xLink,
            jobTitle: $jobTitle,
            companyId: $data['companyId'] ?? null,
            customFields: $customFields,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Convert Contact to array for API requests.
     *
     * @return array
     *   The contact data as array.
     */
    public function toArray(): array
    {
        $data = [];

        // Convert to Twenty CRM format with complex objects
        if ($this->email !== null) {
            $data['emails'] = ['primaryEmail' => $this->email];
        }

        if ($this->phones !== null && !$this->phones->isEmpty()) {
            $data['phones'] = $this->phones->toArray();
        }

        if ($this->mobilePhones !== null && !$this->mobilePhones->isEmpty()) {
            $data['mobilePhones'] = $this->mobilePhones->toArray();
        }

        if ($this->linkedInLink !== null && !$this->linkedInLink->isEmpty()) {
            $data['linkedinLink'] = $this->linkedInLink->toArray();
        }

        if ($this->xLink !== null && !$this->xLink->isEmpty()) {
            $data['xLink'] = $this->xLink->toArray();
        }

        if ($this->firstName !== null || $this->lastName !== null) {
            $data['name'] = [
              'firstName' => $this->firstName,
              'lastName' => $this->lastName,
            ];
        }

        if ($this->jobTitle !== null) {
            $data['jobTitle'] = $this->jobTitle;
        }

        if ($this->companyId !== null) {
            $data['companyId'] = $this->companyId;
        }

        // Add custom fields
        $data = array_merge($data, $this->customFields);

        // Add ID if present (for updates)
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    // Getters

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function getPhones(): ?PhoneCollection
    {
        return $this->phones;
    }

    /**
     * Get primary phone number as string (for backward compatibility).
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phones?->getPrimaryNumber();
    }

    public function getMobilePhones(): ?PhoneCollection
    {
        return $this->mobilePhones;
    }

    /**
     * Get primary mobile phone number as string (for convenience).
     *
     * @return string|null
     */
    public function getMobilePhone(): ?string
    {
        return $this->mobilePhones?->getPrimaryNumber();
    }

    public function getLinkedInLink(): ?LinkCollection
    {
        return $this->linkedInLink;
    }

    /**
     * Get LinkedIn URL as string (for convenience).
     *
     * @return string|null
     */
    public function getLinkedInUrl(): ?string
    {
        return $this->linkedInLink?->getPrimaryLink()?->getUrl();
    }

    public function getXLink(): ?LinkCollection
    {
        return $this->xLink;
    }

    /**
     * Get X (Twitter) URL as string (for convenience).
     *
     * @return string|null
     */
    public function getXUrl(): ?string
    {
        return $this->xLink?->getPrimaryLink()?->getUrl();
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getCustomField(string $key): mixed
    {
        return $this->customFields[$key] ?? null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    // Setters

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setPhones(?PhoneCollection $phones): self
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * Set primary phone number from string (for backward compatibility).
     *
     * @param string|null $phone
     * @return self
     */
    public function setPhone(?string $phone): self
    {
        if ($phone === null) {
            $this->phones = null;
        } else {
            $this->phones = new PhoneCollection(new Phone($phone));
        }

        return $this;
    }

    public function setMobilePhones(?PhoneCollection $mobilePhones): self
    {
        $this->mobilePhones = $mobilePhones;

        return $this;
    }

    /**
     * Set primary mobile phone number from string (for convenience).
     *
     * @param string|null $mobilePhone
     * @return self
     */
    public function setMobilePhone(?string $mobilePhone): self
    {
        if ($mobilePhone === null) {
            $this->mobilePhones = null;
        } else {
            $this->mobilePhones = new PhoneCollection(new Phone($mobilePhone));
        }

        return $this;
    }

    public function setLinkedInLink(?LinkCollection $linkedInLink): self
    {
        $this->linkedInLink = $linkedInLink;

        return $this;
    }

    /**
     * Set LinkedIn URL from string (for convenience).
     *
     * @param string|null $url
     * @param string|null $label
     * @return self
     */
    public function setLinkedInUrl(?string $url, ?string $label = null): self
    {
        if ($url === null) {
            $this->linkedInLink = null;
        } else {
            $this->linkedInLink = new LinkCollection(new Link($url, $label));
        }

        return $this;
    }

    public function setXLink(?LinkCollection $xLink): self
    {
        $this->xLink = $xLink;

        return $this;
    }

    /**
     * Set X (Twitter) URL from string (for convenience).
     *
     * @param string|null $url
     * @param string|null $label
     * @return self
     */
    public function setXUrl(?string $url, ?string $label = null): self
    {
        if ($url === null) {
            $this->xLink = null;
        } else {
            $this->xLink = new LinkCollection(new Link($url, $label));
        }

        return $this;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function setCompanyId(?string $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function setCustomField(string $key, mixed $value): self
    {
        $this->customFields[$key] = $value;

        return $this;
    }

    public function setCustomFields(array $customFields): self
    {
        $this->customFields = $customFields;

        return $this;
    }
}
