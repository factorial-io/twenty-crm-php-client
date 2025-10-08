<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Domain name collection data transfer object.
 */
class DomainNameCollection
{
    /**
     * DomainNameCollection constructor.
     *
     * @param \Factorial\TwentyCrm\DTO\DomainName|null $primaryDomainName
     *   The primary domain name.
     * @param array $additionalDomainNames
     *   Array of additional domain names.
     */
    public function __construct(
        private ?DomainName $primaryDomainName = null,
        private array $additionalDomainNames = [],
    ) {
    }

    /**
     * Create DomainNameCollection from array data.
     *
     * Maps from the Twenty CRM API structure which uses Link-style fields.
     *
     * @param array $data
     *   The domain name collection data.
     *
     * @return self
     *   The DomainNameCollection instance.
     */
    public static function fromArray(array $data): self
    {
        // Create primary domain name
        // API uses 'primaryLinkUrl' for the domain
        $primaryDomainName = null;
        if (isset($data['primaryLinkUrl']) && !empty($data['primaryLinkUrl'])) {
            $primaryDomainName = new DomainName($data['primaryLinkUrl']);
        }

        // Create additional domain names
        // API uses 'secondaryLinks' array
        $additionalDomainNames = [];
        if (isset($data['secondaryLinks']) && is_array($data['secondaryLinks'])) {
            foreach ($data['secondaryLinks'] as $linkData) {
                if (is_array($linkData) && isset($linkData['url'])) {
                    $additionalDomainNames[] = new DomainName($linkData['url']);
                } elseif (is_string($linkData)) {
                    $additionalDomainNames[] = new DomainName($linkData);
                }
            }
        }

        return new self($primaryDomainName, $additionalDomainNames);
    }

    /**
     * Convert DomainNameCollection to array.
     *
     * Maps to the Twenty CRM API structure which uses Link-style fields.
     *
     * @return array
     *   The domain name collection data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->primaryDomainName !== null) {
            $domain = $this->primaryDomainName->getDomain();
            if ($domain !== null) {
                // API expects 'primaryLinkUrl' with the full URL
                $data['primaryLinkUrl'] = $this->primaryDomainName->getUrl();
                // API expects 'primaryLinkLabel' - use plain domain without protocol
                $data['primaryLinkLabel'] = $this->primaryDomainName->getPlainDomain();
            }
        }

        if (!empty($this->additionalDomainNames)) {
            // API expects 'secondaryLinks' array with url/label structure
            $data['secondaryLinks'] = array_map(
                fn ($domainName) => [
                  'url' => $domainName->getUrl(),
                  'label' => $domainName->getPlainDomain(),
                ],
                $this->additionalDomainNames
            );
        }

        return $data;
    }

    // Getters

    public function getPrimaryDomainName(): ?DomainName
    {
        return $this->primaryDomainName;
    }

    public function getAdditionalDomainNames(): array
    {
        return $this->additionalDomainNames;
    }

    public function getAllDomainNames(): array
    {
        $domains = [];
        if ($this->primaryDomainName !== null) {
            $domains[] = $this->primaryDomainName;
        }

        return array_merge($domains, $this->additionalDomainNames);
    }

    public function isEmpty(): bool
    {
        return $this->primaryDomainName === null && empty($this->additionalDomainNames);
    }

    /**
     * Get the primary domain name as string.
     * Falls back to first additional domain if no primary domain exists.
     *
     * @return string|null
     */
    public function getPrimaryDomain(): ?string
    {
        if ($this->primaryDomainName !== null) {
            return $this->primaryDomainName->getDomain();
        }

        // Fallback to first additional domain
        if (!empty($this->additionalDomainNames)) {
            return $this->additionalDomainNames[0]->getDomain();
        }

        return null;
    }

    /**
     * Get the primary domain URL (with protocol).
     *
     * @return string|null
     */
    public function getPrimaryUrl(): ?string
    {
        return $this->primaryDomainName?->getUrl();
    }

    // Setters

    public function setPrimaryDomainName(?DomainName $primaryDomainName): self
    {
        $this->primaryDomainName = $primaryDomainName;

        return $this;
    }

    public function setAdditionalDomainNames(array $additionalDomainNames): self
    {
        $this->additionalDomainNames = $additionalDomainNames;

        return $this;
    }

    public function addAdditionalDomainName(DomainName $domainName): self
    {
        $this->additionalDomainNames[] = $domainName;

        return $this;
    }
}
