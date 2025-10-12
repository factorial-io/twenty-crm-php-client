<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Name data transfer object.
 *
 * Represents a structured name with first and last name components.
 */
class Name implements \JsonSerializable
{
    /**
     * Name constructor.
     *
     * @param string|null $firstName The first name
     * @param string|null $lastName The last name
     */
    public function __construct(
        private ?string $firstName = null,
        private ?string $lastName = null,
    ) {
    }

    /**
     * Create Name from array data.
     *
     * @param array $data The name data
     * @return self The Name instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
        );
    }

    /**
     * Convert Name to array.
     *
     * @return array The name data as array
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }

    /**
     * Get the first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Get the last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Get the full name (first + last).
     *
     * @return string
     */
    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    /**
     * Set the first name.
     *
     * @param string|null $firstName
     * @return self
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Set the last name.
     *
     * @param string|null $lastName
     * @return self
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Check if name is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->firstName) && empty($this->lastName);
    }

    /**
     * JSON serialize the name.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation of the name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullName();
    }
}
