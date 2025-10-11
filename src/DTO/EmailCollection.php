<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Email collection data transfer object.
 */
class EmailCollection
{
    /**
     * EmailCollection constructor.
     *
     * @param string|null $primaryEmail
     *   The primary email address.
     * @param array<string> $additionalEmails
     *   Array of additional email addresses.
     */
    public function __construct(
        private ?string $primaryEmail = null,
        private array $additionalEmails = [],
    ) {
    }

    /**
     * Create EmailCollection from array data.
     *
     * @param array $data
     *   The email collection data from API.
     *
     * @return self
     *   The EmailCollection instance.
     */
    public static function fromArray(array $data): self
    {
        $primaryEmail = $data['primaryEmail'] ?? null;

        // Handle additionalEmails - could be array or null
        $additionalEmails = [];
        if (isset($data['additionalEmails']) && is_array($data['additionalEmails'])) {
            // Filter out empty values
            $additionalEmails = array_filter($data['additionalEmails'], fn($email) => !empty($email));
        }

        return new self($primaryEmail, array_values($additionalEmails));
    }

    /**
     * Convert EmailCollection to array for API.
     *
     * @return array
     *   The email collection data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->primaryEmail !== null && $this->primaryEmail !== '') {
            $data['primaryEmail'] = $this->primaryEmail;
        }

        if (!empty($this->additionalEmails)) {
            $data['additionalEmails'] = array_values($this->additionalEmails);
        }

        return $data;
    }

    // Getters

    /**
     * Get the primary email address.
     *
     * @return string|null
     *   The primary email or null if not set.
     */
    public function getPrimaryEmail(): ?string
    {
        return $this->primaryEmail;
    }

    /**
     * Get additional email addresses.
     *
     * @return array<string>
     *   Array of additional email addresses.
     */
    public function getAdditionalEmails(): array
    {
        return $this->additionalEmails;
    }

    /**
     * Get all email addresses (primary + additional).
     *
     * @return array<string>
     *   Array of all email addresses.
     */
    public function getAllEmails(): array
    {
        $emails = [];

        if ($this->primaryEmail !== null) {
            $emails[] = $this->primaryEmail;
        }

        return array_merge($emails, $this->additionalEmails);
    }

    /**
     * Get all Email objects (primary + additional).
     *
     * @return array<Email>
     *   Array of Email objects.
     */
    public function all(): array
    {
        $emails = [];

        if ($this->primaryEmail !== null) {
            $emails[] = new Email($this->primaryEmail, true);
        }

        foreach ($this->additionalEmails as $email) {
            $emails[] = new Email($email, false);
        }

        return $emails;
    }

    /**
     * Check if collection is empty.
     *
     * @return bool
     *   True if no emails are set.
     */
    public function isEmpty(): bool
    {
        return $this->primaryEmail === null && empty($this->additionalEmails);
    }

    /**
     * Check if a specific email exists in the collection.
     *
     * @param string $email
     *   The email address to check.
     *
     * @return bool
     *   True if email exists.
     */
    public function hasEmail(string $email): bool
    {
        if ($this->primaryEmail === $email) {
            return true;
        }

        return in_array($email, $this->additionalEmails, true);
    }

    /**
     * Count total emails in collection.
     *
     * @return int
     *   Total number of emails.
     */
    public function count(): int
    {
        $count = 0;

        if ($this->primaryEmail !== null) {
            $count++;
        }

        return $count + count($this->additionalEmails);
    }

    // Setters

    /**
     * Set the primary email address.
     *
     * @param string|null $primaryEmail
     *   The primary email.
     *
     * @return self
     */
    public function setPrimaryEmail(?string $primaryEmail): self
    {
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    /**
     * Set additional email addresses.
     *
     * @param array<string> $additionalEmails
     *   Array of additional emails.
     *
     * @return self
     */
    public function setAdditionalEmails(array $additionalEmails): self
    {
        $this->additionalEmails = array_values($additionalEmails);

        return $this;
    }

    /**
     * Add an additional email address.
     *
     * @param string $email
     *   The email to add.
     *
     * @return self
     */
    public function addAdditionalEmail(string $email): self
    {
        if (!in_array($email, $this->additionalEmails, true)) {
            $this->additionalEmails[] = $email;
        }

        return $this;
    }

    /**
     * Remove an email from the collection.
     *
     * @param string $email
     *   The email to remove.
     *
     * @return self
     */
    public function removeEmail(string $email): self
    {
        if ($this->primaryEmail === $email) {
            $this->primaryEmail = null;
        }

        $this->additionalEmails = array_values(
            array_filter($this->additionalEmails, fn($e) => $e !== $email)
        );

        return $this;
    }
}
