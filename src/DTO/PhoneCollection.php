<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Phone collection data transfer object.
 */
class PhoneCollection {

  /**
   * PhoneCollection constructor.
   *
   * @param \Factorial\TwentyCrm\DTO\Phone|null $primaryPhone
   *   The primary phone.
   * @param array $additionalPhones
   *   Array of additional phones.
   */
  public function __construct(
    private ?Phone $primaryPhone = null,
    private array $additionalPhones = [],
  ) {}

  /**
   * Create PhoneCollection from array data.
   *
   * @param array $data
   *   The phone collection data.
   *
   * @return self
   *   The PhoneCollection instance.
   */
  public static function fromArray(array $data): self {
    // Create primary phone
    $primaryPhone = null;
    if (isset($data['primaryPhoneNumber']) || isset($data['primaryPhoneCountryCode']) || isset($data['primaryPhoneCallingCode'])) {
      $primaryPhone = Phone::fromArray([
        'primaryPhoneNumber' => $data['primaryPhoneNumber'] ?? null,
        'primaryPhoneCountryCode' => $data['primaryPhoneCountryCode'] ?? null,
        'primaryPhoneCallingCode' => $data['primaryPhoneCallingCode'] ?? null,
      ]);
    }

    // Create additional phones
    $additionalPhones = [];
    if (isset($data['additionalPhones']) && is_array($data['additionalPhones'])) {
      foreach ($data['additionalPhones'] as $phoneData) {
        if (is_array($phoneData)) {
          $additionalPhones[] = Phone::fromArray($phoneData);
        } elseif (is_string($phoneData)) {
          // Handle simple string phone numbers
          $additionalPhones[] = new Phone($phoneData);
        }
      }
    }

    return new self($primaryPhone, $additionalPhones);
  }

  /**
   * Convert PhoneCollection to array.
   *
   * @return array
   *   The phone collection data as array.
   */
  public function toArray(): array {
    $data = [];

    if ($this->primaryPhone !== null) {
      if ($this->primaryPhone->getNumber() !== null) {
        $data['primaryPhoneNumber'] = $this->primaryPhone->getNumber();
      }
      if ($this->primaryPhone->getCountryCode() !== null) {
        $data['primaryPhoneCountryCode'] = $this->primaryPhone->getCountryCode();
      }
      if ($this->primaryPhone->getCallingCode() !== null) {
        $data['primaryPhoneCallingCode'] = $this->primaryPhone->getCallingCode();
      }
    }

    if (!empty($this->additionalPhones)) {
      $data['additionalPhones'] = array_map(fn($phone) => $phone->toArray(), $this->additionalPhones);
    }

    return $data;
  }

  // Getters

  public function getPrimaryPhone(): ?Phone {
    return $this->primaryPhone;
  }

  public function getAdditionalPhones(): array {
    return $this->additionalPhones;
  }

  public function getAllPhones(): array {
    $phones = [];
    if ($this->primaryPhone !== null) {
      $phones[] = $this->primaryPhone;
    }
    return array_merge($phones, $this->additionalPhones);
  }

  public function isEmpty(): bool {
    return $this->primaryPhone === null && empty($this->additionalPhones);
  }

  /**
   * Get the primary phone number as string.
   * Falls back to first additional phone if no primary phone exists.
   *
   * @return string|null
   */
  public function getPrimaryNumber(): ?string {
    if ($this->primaryPhone !== null) {
      return $this->primaryPhone->getNumber();
    }

    // Fallback to first additional phone
    if (!empty($this->additionalPhones)) {
      return $this->additionalPhones[0]->getNumber();
    }

    return null;
  }

  /**
   * Get the primary phone formatted (with calling code).
   *
   * @return string|null
   */
  public function getPrimaryFormatted(): ?string {
    return $this->primaryPhone?->getFormatted();
  }

  // Setters

  public function setPrimaryPhone(?Phone $primaryPhone): self {
    $this->primaryPhone = $primaryPhone;
    return $this;
  }

  public function setAdditionalPhones(array $additionalPhones): self {
    $this->additionalPhones = $additionalPhones;
    return $this;
  }

  public function addAdditionalPhone(Phone $phone): self {
    $this->additionalPhones[] = $phone;
    return $this;
  }

}
