<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Email data transfer object.
 */
class Email
{
    /**
     * Email constructor.
     *
     * @param string $email
     *   The email address.
     * @param bool $isPrimary
     *   Whether this is the primary email.
     */
    public function __construct(
        private string $email,
        private bool $isPrimary = false,
    ) {
    }

    /**
     * Create Email from string.
     *
     * @param string $email
     *   The email address.
     * @param bool $isPrimary
     *   Whether this is the primary email.
     *
     * @return self
     *   The Email instance.
     */
    public static function fromString(string $email, bool $isPrimary = false): self
    {
        return new self($email, $isPrimary);
    }

    /**
     * Get the email address.
     *
     * @return string
     *   The email address.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Check if this is the primary email.
     *
     * @return bool
     *   True if primary email.
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Set the email address.
     *
     * @param string $email
     *   The email address.
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set whether this is the primary email.
     *
     * @param bool $isPrimary
     *   True if primary.
     *
     * @return self
     */
    public function setPrimary(bool $isPrimary): self
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * Convert to string.
     *
     * @return string
     *   The email address.
     */
    public function __toString(): string
    {
        return $this->email;
    }
}
