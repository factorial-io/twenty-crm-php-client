<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Address data transfer object.
 *
 * Represents a structured address with multiple components.
 */
class Address implements \JsonSerializable
{
    /**
     * Address constructor.
     *
     * @param string|null $addressStreet1 Street address line 1
     * @param string|null $addressStreet2 Street address line 2
     * @param string|null $addressCity City
     * @param string|null $addressState State/Province
     * @param string|null $addressPostCode Postal/ZIP code
     * @param string|null $addressCountry Country
     * @param float|null $addressLat Latitude
     * @param float|null $addressLng Longitude
     */
    public function __construct(
        private ?string $addressStreet1 = null,
        private ?string $addressStreet2 = null,
        private ?string $addressCity = null,
        private ?string $addressState = null,
        private ?string $addressPostCode = null,
        private ?string $addressCountry = null,
        private ?float $addressLat = null,
        private ?float $addressLng = null,
    ) {
    }

    /**
     * Create Address from array data.
     *
     * @param array $data The address data
     * @return self The Address instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            addressStreet1: $data['addressStreet1'] ?? null,
            addressStreet2: $data['addressStreet2'] ?? null,
            addressCity: $data['addressCity'] ?? null,
            addressState: $data['addressState'] ?? null,
            // Twenty CRM API returns 'addressPostcode' (lowercase 'c'), but we also
            // support 'addressPostCode' (camelCase) for consistency with other fields.
            addressPostCode: $data['addressPostCode'] ?? $data['addressPostcode'] ?? null,
            addressCountry: $data['addressCountry'] ?? null,
            addressLat: isset($data['addressLat']) ? (float) $data['addressLat'] : null,
            addressLng: isset($data['addressLng']) ? (float) $data['addressLng'] : null,
        );
    }

    /**
     * Convert Address to array.
     *
     * @return array The address data as array
     */
    public function toArray(): array
    {
        return array_filter([
            'addressStreet1' => $this->addressStreet1,
            'addressStreet2' => $this->addressStreet2,
            'addressCity' => $this->addressCity,
            'addressState' => $this->addressState,
            'addressPostCode' => $this->addressPostCode,
            'addressCountry' => $this->addressCountry,
            'addressLat' => $this->addressLat,
            'addressLng' => $this->addressLng,
        ], fn ($value) => $value !== null);
    }

    // Getters
    public function getStreet1(): ?string
    {
        return $this->addressStreet1;
    }

    public function getStreet2(): ?string
    {
        return $this->addressStreet2;
    }

    public function getCity(): ?string
    {
        return $this->addressCity;
    }

    public function getState(): ?string
    {
        return $this->addressState;
    }

    public function getPostCode(): ?string
    {
        return $this->addressPostCode;
    }

    public function getCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function getLatitude(): ?float
    {
        return $this->addressLat;
    }

    public function getLongitude(): ?float
    {
        return $this->addressLng;
    }

    // Setters
    public function setStreet1(?string $street1): self
    {
        $this->addressStreet1 = $street1;
        return $this;
    }

    public function setStreet2(?string $street2): self
    {
        $this->addressStreet2 = $street2;
        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->addressCity = $city;
        return $this;
    }

    public function setState(?string $state): self
    {
        $this->addressState = $state;
        return $this;
    }

    public function setPostCode(?string $postCode): self
    {
        $this->addressPostCode = $postCode;
        return $this;
    }

    public function setCountry(?string $country): self
    {
        $this->addressCountry = $country;
        return $this;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->addressLat = $latitude;
        return $this;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->addressLng = $longitude;
        return $this;
    }

    /**
     * Get formatted address string.
     *
     * @return string
     */
    public function getFormatted(): string
    {
        $parts = array_filter([
            $this->addressStreet1,
            $this->addressStreet2,
            $this->addressCity,
            $this->addressState,
            $this->addressPostCode,
            $this->addressCountry,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if address is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->addressStreet1)
            && empty($this->addressStreet2)
            && empty($this->addressCity)
            && empty($this->addressState)
            && empty($this->addressPostCode)
            && empty($this->addressCountry);
    }

    /**
     * JSON serialize the address.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation of the address.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }
}
