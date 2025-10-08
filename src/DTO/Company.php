<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Company data transfer object.
 */
class Company
{
    /**
     * Company constructor.
     *
     * @param string|null $id
     *   The company ID.
     * @param string|null $name
     *   The company name.
     * @param \Factorial\TwentyCrm\DTO\DomainNameCollection|null $domainName
     *   The company domain names.
     * @param \Factorial\TwentyCrm\DTO\LinkCollection|null $facebook
     *   The company Facebook links.
     * @param \Factorial\TwentyCrm\DTO\LinkCollection|null $linkedinLink
     *   The company LinkedIn links.
     * @param \Factorial\TwentyCrm\DTO\LinkCollection|null $xLink
     *   The company X (Twitter) links.
     * @param string|null $addressCity
     *   The company city.
     * @param string|null $addressCountry
     *   The company country.
     * @param string|null $addressStreet1
     *   The company street address line 1.
     * @param string|null $addressStreet2
     *   The company street address line 2.
     * @param string|null $addressState
     *   The company state.
     * @param string|null $addressPostcode
     *   The company postcode.
     * @param int|null $employees
     *   Number of employees.
     * @param array $customFields
     *   Custom field values.
     * @param \DateTimeInterface|null $createdAt
     *   Creation timestamp.
     * @param \DateTimeInterface|null $updatedAt
     *   Last update timestamp.
     */
    public function __construct(
        private ?string $id = null,
        private ?string $name = null,
        private ?DomainNameCollection $domainName = null,
        private ?LinkCollection $facebook = null,
        private ?LinkCollection $linkedinLink = null,
        private ?LinkCollection $xLink = null,
        private ?string $addressCity = null,
        private ?string $addressCountry = null,
        private ?string $addressStreet1 = null,
        private ?string $addressStreet2 = null,
        private ?string $addressState = null,
        private ?string $addressPostcode = null,
        private ?int $employees = null,
        private array $customFields = [],
        private ?\DateTimeInterface $createdAt = null,
        private ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    /**
     * Create Company from API response array.
     *
     * @param array $data
     *   The API response data.
     *
     * @return self
     *   The Company instance.
     */
    public static function fromArray(array $data): self
    {
        $createdAt = isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null;
        $updatedAt = isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null;

        // Extract address fields from nested address object
        $addressCity = $data['address']['addressCity'] ?? null;
        $addressCountry = $data['address']['addressCountry'] ?? null;
        $addressStreet1 = $data['address']['addressStreet1'] ?? null;
        $addressStreet2 = $data['address']['addressStreet2'] ?? null;
        $addressState = $data['address']['addressState'] ?? null;
        $addressPostcode = $data['address']['addressPostcode'] ?? null;

        // Extract domain names
        $domainName = null;
        if (isset($data['domainName']) && is_array($data['domainName'])) {
            $domainName = DomainNameCollection::fromArray($data['domainName']);
        }

        // Extract Facebook links
        $facebook = null;
        if (isset($data['facebook']) && is_array($data['facebook'])) {
            $facebook = LinkCollection::fromArray($data['facebook']);
        }

        // Extract LinkedIn links
        $linkedinLink = null;
        if (isset($data['linkedinLink']) && is_array($data['linkedinLink'])) {
            $linkedinLink = LinkCollection::fromArray($data['linkedinLink']);
        }

        // Extract X (Twitter) links
        $xLink = null;
        if (isset($data['xLink']) && is_array($data['xLink'])) {
            $xLink = LinkCollection::fromArray($data['xLink']);
        }

        // Extract standard fields for Twenty CRM
        // Also exclude read-only/computed fields that shouldn't be in customFields
        $standardFields = [
          'id', 'name', 'domainName', 'facebook', 'linkedinLink', 'xLink', 'address', 'employees',
          'createdAt', 'updatedAt', 'deletedAt', 'annualRecurringRevenue', 'idealCustomerProfile',
          // Read-only/computed fields
          'searchVector', 'createdBy', 'position', 'phones', 'webTechnologies', 'annualRevenue',
          'industry', 'hubspotId', 'yearFounded', 'description', 'timezone', 'linkedinBio',
          'isPublic', 'lifecycleStage', 'originalTrafficSource', 'accountOwnerId', 'lastActivityDate',
        ];
        $customFields = array_diff_key($data, array_flip($standardFields));

        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            domainName: $domainName,
            facebook: $facebook,
            linkedinLink: $linkedinLink,
            xLink: $xLink,
            addressCity: $addressCity,
            addressCountry: $addressCountry,
            addressStreet1: $addressStreet1,
            addressStreet2: $addressStreet2,
            addressState: $addressState,
            addressPostcode: $addressPostcode,
            employees: isset($data['employees']) ? (int) $data['employees'] : null,
            customFields: $customFields,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Convert Company to array for API requests.
     *
     * @return array
     *   The company data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->domainName !== null && !$this->domainName->isEmpty()) {
            $data['domainName'] = $this->domainName->toArray();
        }

        if ($this->facebook !== null && !$this->facebook->isEmpty()) {
            $data['facebook'] = $this->facebook->toArray();
        }

        if ($this->linkedinLink !== null && !$this->linkedinLink->isEmpty()) {
            $data['linkedinLink'] = $this->linkedinLink->toArray();
        }

        if ($this->xLink !== null && !$this->xLink->isEmpty()) {
            $data['xLink'] = $this->xLink->toArray();
        }

        // Build address object if any address fields are set
        $hasAddress = $this->addressCity !== null ||
                      $this->addressCountry !== null ||
                      $this->addressStreet1 !== null ||
                      $this->addressStreet2 !== null ||
                      $this->addressState !== null ||
                      $this->addressPostcode !== null;

        if ($hasAddress) {
            $data['address'] = [
              'addressCity' => $this->addressCity,
              'addressCountry' => $this->addressCountry,
              'addressStreet1' => $this->addressStreet1,
              'addressStreet2' => $this->addressStreet2,
              'addressState' => $this->addressState,
              'addressPostcode' => $this->addressPostcode,
            ];
        }

        if ($this->employees !== null) {
            $data['employees'] = $this->employees;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDomainName(): ?DomainNameCollection
    {
        return $this->domainName;
    }

    public function getFacebook(): ?LinkCollection
    {
        return $this->facebook;
    }

    public function getLinkedinLink(): ?LinkCollection
    {
        return $this->linkedinLink;
    }

    public function getXLink(): ?LinkCollection
    {
        return $this->xLink;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function getAddressStreet1(): ?string
    {
        return $this->addressStreet1;
    }

    public function getAddressStreet2(): ?string
    {
        return $this->addressStreet2;
    }

    public function getAddressState(): ?string
    {
        return $this->addressState;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function getEmployees(): ?int
    {
        return $this->employees;
    }

    public function getLocationString(): string
    {
        $parts = array_filter([
          $this->addressCity,
          $this->addressCountry,
        ]);

        return implode(', ', $parts);
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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDomainName(?DomainNameCollection $domainName): self
    {
        $this->domainName = $domainName;

        return $this;
    }

    public function setFacebook(?LinkCollection $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function setLinkedinLink(?LinkCollection $linkedinLink): self
    {
        $this->linkedinLink = $linkedinLink;

        return $this;
    }

    public function setXLink(?LinkCollection $xLink): self
    {
        $this->xLink = $xLink;

        return $this;
    }

    public function setAddressCity(?string $addressCity): self
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function setAddressCountry(?string $addressCountry): self
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function setAddressStreet1(?string $addressStreet1): self
    {
        $this->addressStreet1 = $addressStreet1;

        return $this;
    }

    public function setAddressStreet2(?string $addressStreet2): self
    {
        $this->addressStreet2 = $addressStreet2;

        return $this;
    }

    public function setAddressState(?string $addressState): self
    {
        $this->addressState = $addressState;

        return $this;
    }

    public function setAddressPostcode(?string $addressPostcode): self
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function setEmployees(?int $employees): self
    {
        $this->employees = $employees;

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
