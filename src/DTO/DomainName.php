<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Domain name data transfer object.
 */
class DomainName
{
    /**
     * DomainName constructor.
     *
     * @param string|null $domain
     *   The domain name (e.g., "example.com" or "https://example.com").
     */
    public function __construct(
        private ?string $domain = null,
    ) {
    }

    /**
     * Create DomainName from array data.
     *
     * @param array $data
     *   The domain name data.
     *
     * @return self
     *   The DomainName instance.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'] ?? $data['url'] ?? null,
        );
    }

    /**
     * Convert DomainName to array.
     *
     * @return array
     *   The domain name data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->domain !== null) {
            $data['domain'] = $this->domain;
        }

        return $data;
    }

    // Getters

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Get the domain with protocol (ensures https:// prefix).
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        if ($this->domain === null) {
            return null;
        }

        // If already has protocol, return as-is
        if (preg_match('/^https?:\/\//', $this->domain)) {
            return $this->domain;
        }

        // Add https:// prefix
        return 'https://' . $this->domain;
    }

    /**
     * Get the domain without protocol.
     *
     * @return string|null
     */
    public function getPlainDomain(): ?string
    {
        if ($this->domain === null) {
            return null;
        }

        // Strip protocol if present
        return preg_replace('/^https?:\/\//', '', $this->domain);
    }

    // Setters

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }
}
