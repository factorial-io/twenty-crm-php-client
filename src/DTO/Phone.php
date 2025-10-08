<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Phone data transfer object.
 */
class Phone
{
    /**
     * Phone constructor.
     *
     * @param string|null $number
     *   The phone number.
     * @param string|null $countryCode
     *   The country code (e.g., "US", "DE").
     * @param string|null $callingCode
     *   The calling code (e.g., "+1", "+49").
     */
    public function __construct(
        private ?string $number = null,
        private ?string $countryCode = null,
        private ?string $callingCode = null,
    ) {
    }

    /**
     * Create Phone from array data.
     *
     * @param array $data
     *   The phone data.
     *
     * @return self
     *   The Phone instance.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'] ?? $data['primaryPhoneNumber'] ?? null,
            countryCode: $data['countryCode'] ?? $data['primaryPhoneCountryCode'] ?? null,
            callingCode: $data['callingCode'] ?? $data['primaryPhoneCallingCode'] ?? null,
        );
    }

    /**
     * Convert Phone to array.
     *
     * @return array
     *   The phone data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->number !== null) {
            $data['number'] = $this->number;
        }

        if ($this->countryCode !== null) {
            $data['countryCode'] = $this->countryCode;
        }

        if ($this->callingCode !== null) {
            $data['callingCode'] = $this->callingCode;
        }

        return $data;
    }

    // Getters

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function getCallingCode(): ?string
    {
        return $this->callingCode;
    }

    /**
     * Get formatted phone number.
     *
     * @return string|null
     *   The formatted phone number with calling code if available.
     */
    public function getFormatted(): ?string
    {
        if ($this->number === null) {
            return null;
        }

        if ($this->callingCode !== null) {
            return $this->callingCode . $this->number;
        }

        return $this->number;
    }

    // Setters

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function setCallingCode(?string $callingCode): self
    {
        $this->callingCode = $callingCode;

        return $this;
    }
}
