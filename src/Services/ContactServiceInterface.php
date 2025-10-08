<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Services;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\ContactCollection;
use Factorial\TwentyCrm\DTO\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;

/**
 * Interface for contact service operations.
 */
interface ContactServiceInterface
{
    /**
     * Find contacts based on search criteria.
     *
     * @param \Factorial\TwentyCrm\DTO\FilterInterface $filter
     *   The search filter criteria.
     * @param \Factorial\TwentyCrm\DTO\SearchOptions $options
     *   The search options.
     *
     * @return ContactCollection
     *   The search results with contacts and pagination info.
     */
    public function find(FilterInterface $filter, SearchOptions $options): ContactCollection;

    /**
     * Get a contact by ID.
     *
     * @param string $id
     *   The contact ID.
     *
     * @return Contact|null
     *   The contact or NULL if not found.
     */
    public function getById(string $id): ?Contact;

    /**
     * Create a new contact.
     *
     * @param Contact $contact
     *   The contact to create.
     *
     * @return Contact
     *   The created contact with ID.
     */
    public function create(Contact $contact): Contact;

    /**
     * Update an existing contact.
     *
     * @param Contact $contact
     *   The contact to update (must have ID).
     *
     * @return Contact
     *   The updated contact.
     */
    public function update(Contact $contact): Contact;

    /**
     * Delete a contact.
     *
     * @param string $id
     *   The contact ID.
     *
     * @return bool
     *   True if deleted successfully.
     */
    public function delete(string $id): bool;

    /**
     * Batch create or update contacts.
     *
     * @param Contact[] $contacts
     *   Array of contacts.
     *
     * @return ContactCollection
     *   Collection of created/updated contacts.
     */
    public function batchUpsert(array $contacts): ContactCollection;

    /**
     * Get available fields for contacts.
     *
     * @return array
     *   Array of field definitions.
     */
    public function getFields(): array;

    /**
     * Find contact by email.
     *
     * @param string $email
     *   The email address.
     *
     * @return Contact|null
     *   The contact or NULL if not found.
     */
    public function findByEmail(string $email): ?Contact;
}
