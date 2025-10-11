<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

/**
 * Metadata for an entity relation in Twenty CRM.
 *
 * Represents relationships between entities (e.g., Person → Company,
 * Company → People, Campaign ↔ People).
 */
class RelationMetadata
{
    /**
     * @param string $name The relation name (e.g., 'company', 'people', 'campaigns')
     * @param string $type The relation type ('MANY_TO_ONE', 'ONE_TO_MANY', 'MANY_TO_MANY')
     * @param string $targetEntity The target entity name (e.g., 'company', 'person', 'campaign')
     * @param string $foreignKey The foreign key field name (e.g., 'companyId', 'personId')
     * @param string|null $inverseName The name of the inverse relation (if bidirectional)
     * @param bool $isNullable Whether the relation can be null
     * @param bool $isCustom Whether this is a custom relation
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $targetEntity,
        public readonly string $foreignKey,
        public readonly ?string $inverseName = null,
        public readonly bool $isNullable = true,
        public readonly bool $isCustom = false,
    ) {
    }

    /**
     * Check if this is a MANY_TO_ONE relation.
     *
     * @return bool
     */
    public function isManyToOne(): bool
    {
        return $this->type === 'MANY_TO_ONE';
    }

    /**
     * Check if this is a ONE_TO_MANY relation.
     *
     * @return bool
     */
    public function isOneToMany(): bool
    {
        return $this->type === 'ONE_TO_MANY';
    }

    /**
     * Check if this is a MANY_TO_MANY relation.
     *
     * @return bool
     */
    public function isManyToMany(): bool
    {
        return $this->type === 'MANY_TO_MANY';
    }
}
