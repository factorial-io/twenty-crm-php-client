<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Metadata;

use Factorial\TwentyCrm\Enums\RelationType;

/**
 * Metadata for an entity relation in Twenty CRM.
 *
 * Represents relationships between entities (e.g., Person → Company,
 * Company → People, Campaign ↔ People).
 *
 * Captures the complete relation structure as defined in the Twenty CRM API:
 * - Field information (name, label, description)
 * - Relation type (ONE_TO_MANY, MANY_TO_ONE, MANY_TO_MANY, ONE_TO_ONE)
 * - Source and target entities
 * - Bidirectional relation information
 */
final class RelationMetadata
{
    /**
     * @param string $name The relation field name (e.g., 'company', 'people', 'campaigns')
     * @param string $label Human-readable label for the field
     * @param RelationType $type The relation type
     * @param string $sourceObjectName Name of the source entity (e.g., 'person')
     * @param string $targetObjectName Name of the target entity (e.g., 'company')
     * @param string $targetFieldName Name of the relation field on the target entity
     * @param bool $isNullable Whether the relation can be null
     * @param bool $isSystem Whether this is a system relation
     * @param bool $isActive Whether this relation is currently active
     * @param bool $isCustom Whether this is a custom relation
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly RelationType $type,
        public readonly string $sourceObjectName,
        public readonly string $targetObjectName,
        public readonly string $targetFieldName,
        public readonly bool $isNullable = true,
        public readonly bool $isSystem = false,
        public readonly bool $isActive = true,
        public readonly bool $isCustom = false,
    ) {
    }

    /**
     * Create from Twenty CRM API metadata field.
     *
     * @param array<string, mixed> $fieldMetadata Field metadata from API
     * @return self|null Relation metadata or null if not a relation field
     */
    public static function fromApiMetadata(array $fieldMetadata): ?self
    {
        // Check if this is a relation field
        if (($fieldMetadata['type'] ?? null) !== 'RELATION' || !isset($fieldMetadata['relation'])) {
            return null;
        }

        $relation = $fieldMetadata['relation'];

        // Parse relation type from API string to enum
        $relationType = RelationType::tryFrom($relation['type']);
        if (!$relationType) {
            return null; // Unknown relation type
        }

        return new self(
            name: $fieldMetadata['name'],
            label: $fieldMetadata['label'] ?? $fieldMetadata['name'],
            type: $relationType,
            sourceObjectName: $relation['sourceObjectMetadata']['nameSingular'],
            targetObjectName: $relation['targetObjectMetadata']['nameSingular'],
            targetFieldName: $relation['targetFieldMetadata']['name'],
            isNullable: $fieldMetadata['isNullable'] ?? true,
            isSystem: $fieldMetadata['isSystem'] ?? false,
            isActive: $fieldMetadata['isActive'] ?? true,
            isCustom: $fieldMetadata['isCustom'] ?? false,
        );
    }

    /**
     * Check if this is a MANY_TO_ONE relation.
     *
     * @return bool
     */
    public function isManyToOne(): bool
    {
        return $this->type === RelationType::MANY_TO_ONE;
    }

    /**
     * Check if this is a ONE_TO_MANY relation.
     *
     * @return bool
     */
    public function isOneToMany(): bool
    {
        return $this->type === RelationType::ONE_TO_MANY;
    }

    /**
     * Check if this is a MANY_TO_MANY relation.
     *
     * @return bool
     */
    public function isManyToMany(): bool
    {
        return $this->type === RelationType::MANY_TO_MANY;
    }

    /**
     * Check if this is a ONE_TO_ONE relation.
     *
     * @return bool
     */
    public function isOneToOne(): bool
    {
        return $this->type === RelationType::ONE_TO_ONE;
    }

    /**
     * Check if this relation returns multiple entities (collection).
     *
     * @return bool
     */
    public function returnsCollection(): bool
    {
        return $this->type->returnsCollection();
    }

    /**
     * Check if this relation returns a single entity.
     *
     * @return bool
     */
    public function returnsSingleEntity(): bool
    {
        return $this->type->returnsSingleEntity();
    }
}
