<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Contact data transfer object.
 */
class Contact {

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
   * @param string|null $phone
   *   The contact phone number.
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
    private ?string $phone = null,
    private ?string $companyId = null,
    private array $customFields = [],
    private ?\DateTimeInterface $createdAt = null,
    private ?\DateTimeInterface $updatedAt = null,
  ) {}

  /**
   * Create Contact from API response array.
   *
   * @param array $data
   *   The API response data.
   *
   * @return self
   *   The Contact instance.
   */
  public static function fromArray(array $data): self {
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
    
    // Extract phone from phones object (primaryPhoneNumber or first phone)
    $phone = null;
    if (isset($data['phones']['primaryPhoneNumber'])) {
      $phone = $data['phones']['primaryPhoneNumber'];
    } elseif (isset($data['phones']['additionalPhones'][0])) {
      $phone = $data['phones']['additionalPhones'][0];
    }
    
    // Extract names from name object
    $firstName = $data['name']['firstName'] ?? null;
    $lastName = $data['name']['lastName'] ?? null;
    
    // Extract standard fields for Twenty CRM
    $standardFields = ['id', 'emails', 'phones', 'name', 'companyId', 'createdAt', 'updatedAt', 'deletedAt', 'jobTitle', 'city', 'avatarUrl'];
    $customFields = array_diff_key($data, array_flip($standardFields));
    
    return new self(
      id: $data['id'] ?? null,
      email: $email,
      firstName: $firstName,
      lastName: $lastName,
      phone: $phone,
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
  public function toArray(): array {
    $data = [];
    
    // Convert to Twenty CRM format with complex objects
    if ($this->email !== null) {
      $data['emails'] = ['primaryEmail' => $this->email];
    }
    
    if ($this->phone !== null) {
      $data['phones'] = ['primaryPhoneNumber' => $this->phone];
    }
    
    if ($this->firstName !== null || $this->lastName !== null) {
      $data['name'] = [
        'firstName' => $this->firstName,
        'lastName' => $this->lastName,
      ];
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

  public function getId(): ?string {
    return $this->id;
  }

  public function getEmail(): ?string {
    return $this->email;
  }

  public function getFirstName(): ?string {
    return $this->firstName;
  }

  public function getLastName(): ?string {
    return $this->lastName;
  }

  public function getFullName(): string {
    return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
  }

  public function getPhone(): ?string {
    return $this->phone;
  }

  public function getCompanyId(): ?string {
    return $this->companyId;
  }

  public function getCustomFields(): array {
    return $this->customFields;
  }

  public function getCustomField(string $key): mixed {
    return $this->customFields[$key] ?? null;
  }

  public function getCreatedAt(): ?\DateTimeInterface {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?\DateTimeInterface {
    return $this->updatedAt;
  }

  // Setters

  public function setId(?string $id): self {
    $this->id = $id;
    return $this;
  }

  public function setEmail(?string $email): self {
    $this->email = $email;
    return $this;
  }

  public function setFirstName(?string $firstName): self {
    $this->firstName = $firstName;
    return $this;
  }

  public function setLastName(?string $lastName): self {
    $this->lastName = $lastName;
    return $this;
  }

  public function setPhone(?string $phone): self {
    $this->phone = $phone;
    return $this;
  }

  public function setCompanyId(?string $companyId): self {
    $this->companyId = $companyId;
    return $this;
  }

  public function setCustomField(string $key, mixed $value): self {
    $this->customFields[$key] = $value;
    return $this;
  }

  public function setCustomFields(array $customFields): self {
    $this->customFields = $customFields;
    return $this;
  }

}