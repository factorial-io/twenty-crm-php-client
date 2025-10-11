<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

use Factorial\TwentyCrm\DTO\LinkCollection;

/**
 * Handler for LINKS field type.
 *
 * Transforms between Twenty CRM link array format and LinkCollection objects.
 *
 * API Format:
 * ```
 * [
 *     'primaryLinkUrl' => 'https://linkedin.com/in/johndoe',
 *     'primaryLinkLabel' => 'LinkedIn',
 *     'secondaryLinks' => [
 *         ['url' => 'https://twitter.com/johndoe', 'label' => 'Twitter']
 *     ]
 * ]
 * ```
 */
class LinksFieldHandler implements NestedObjectHandler
{
    public function fromApi(array $data): ?LinkCollection
    {
        if (empty($data)) {
            return null;
        }

        return LinkCollection::fromArray($data);
    }

    public function toApi(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof LinkCollection) {
            return $value->toArray();
        }

        // If already an array, pass through
        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    public function getPhpType(): string
    {
        return '?' . LinkCollection::class;
    }

    public function getFieldType(): string
    {
        return 'LINKS';
    }
}
