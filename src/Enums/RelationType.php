<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Enums;

/**
 * Enum for entity relation types in Twenty CRM.
 */
enum RelationType: string
{
    case ONE_TO_MANY = 'ONE_TO_MANY';
    case MANY_TO_ONE = 'MANY_TO_ONE';
    case MANY_TO_MANY = 'MANY_TO_MANY';
    case ONE_TO_ONE = 'ONE_TO_ONE';

    /**
     * Check if this relation returns a collection (array of entities).
     */
    public function returnsCollection(): bool
    {
        return $this === self::ONE_TO_MANY || $this === self::MANY_TO_MANY;
    }

    /**
     * Check if this relation returns a single entity.
     */
    public function returnsSingleEntity(): bool
    {
        return $this === self::MANY_TO_ONE || $this === self::ONE_TO_ONE;
    }
}
