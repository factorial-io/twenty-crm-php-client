<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Company data transfer object.
 */
class Company {

  /**
   * Company constructor.
   *
   * @param string|null $id
   *   The company ID.
   * @param string|null $name
   *   The company name.
   * @param string|null $domain
   *   The company domain/website.
   * @param string|null $address
   *   The company address.
   * @param string|null $city
   *   The company city.
   * @param string|null $country
   *   The company country.
   * @param int|null $employeeCount
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
    private ?string $domain = null,
    private ?string $address = null,
    private ?string $city = null,
    private ?string $country = null,
    private ?int $employeeCount = null,
    private array $customFields = [],
    private ?\DateTimeInterface $createdAt = null,
    private ?\DateTimeInterface $updatedAt = null,
  ) {}

  /**
   * Create Company from API response array.
   *
   * @param array $data
   *   The API response data.
   *
   * @return self
   *   The Company instance.
   */
  public static function fromArray(array $data): self {
    $createdAt = isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null;
    $updatedAt = isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null;
    
    // Extract standard fields
    $standardFields = ['id', 'name', 'domain', 'address', 'city', 'country', 'employeeCount', 'createdAt', 'updatedAt'];
    $customFields = array_diff_key($data, array_flip($standardFields));
    
    return new self(
      id: $data['id'] ?? null,
      name: $data['name'] ?? null,
      domain: $data['domain'] ?? null,
      address: $data['address'] ?? null,
      city: $data['city'] ?? null,
      country: $data['country'] ?? null,
      employeeCount: isset($data['employeeCount']) ? (int) $data['employeeCount'] : null,
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
  public function toArray(): array {
    $data = array_filter([
      'name' => $this->name,
      'domain' => $this->domain,
      'address' => $this->address,
      'city' => $this->city,
      'country' => $this->country,
      'employeeCount' => $this->employeeCount,
    ], fn($value) => $value !== null);
    
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

  public function getName(): ?string {
    return $this->name;
  }

  public function getDomain(): ?string {
    return $this->domain;
  }

  public function getAddress(): ?string {
    return $this->address;
  }

  public function getCity(): ?string {
    return $this->city;
  }

  public function getCountry(): ?string {
    return $this->country;
  }

  public function getEmployeeCount(): ?int {
    return $this->employeeCount;
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

  public function setName(?string $name): self {
    $this->name = $name;
    return $this;
  }

  public function setDomain(?string $domain): self {
    $this->domain = $domain;
    return $this;
  }

  public function setAddress(?string $address): self {
    $this->address = $address;
    return $this;
  }

  public function setCity(?string $city): self {
    $this->city = $city;
    return $this;
  }

  public function setCountry(?string $country): self {
    $this->country = $country;
    return $this;
  }

  public function setEmployeeCount(?int $employeeCount): self {
    $this->employeeCount = $employeeCount;
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