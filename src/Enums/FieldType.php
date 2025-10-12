<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Enums;

/**
 * Enum for field types in Twenty CRM.
 *
 * These represent the different data types that fields can have
 * in the Twenty CRM API metadata.
 */
enum FieldType: string
{
    // Basic types
    case TEXT = 'TEXT';
    case NUMBER = 'NUMBER';
    case BOOLEAN = 'BOOLEAN';
    case UUID = 'UUID';
    case DATE_TIME = 'DATE_TIME';
    case DATE = 'DATE';
    case POSITION = 'POSITION';

    // Complex types
    case SELECT = 'SELECT';
    case MULTI_SELECT = 'MULTI_SELECT';
    case RELATION = 'RELATION';
    case EMAILS = 'EMAILS';
    case PHONES = 'PHONES';
    case LINKS = 'LINKS';
    case FULL_NAME = 'FULL_NAME';
    case ADDRESS = 'ADDRESS';
    case CURRENCY = 'CURRENCY';
    case ACTOR = 'ACTOR';
    case RATING = 'RATING';

    // System types
    case TS_VECTOR = 'TS_VECTOR';
    case RAW_JSON = 'RAW_JSON';

    /**
     * Check if this field type requires a nested object handler.
     */
    public function requiresNestedObjectHandler(): bool
    {
        return match ($this) {
            self::EMAILS,
            self::PHONES,
            self::LINKS,
            self::FULL_NAME,
            self::ADDRESS,
            self::CURRENCY => true,
            default => false,
        };
    }

    /**
     * Check if this is a relation field type.
     */
    public function isRelation(): bool
    {
        return $this === self::RELATION;
    }

    /**
     * Check if this is a system-managed field type.
     */
    public function isSystemType(): bool
    {
        return match ($this) {
            self::TS_VECTOR,
            self::ACTOR,
            self::POSITION => true,
            default => false,
        };
    }

    /**
     * Get the PHP type for this field type.
     *
     * Returns the native PHP type for fields that don't have a handler.
     */
    public function getPhpType(): string
    {
        return match ($this) {
            self::TEXT,
            self::UUID => 'string',
            self::NUMBER,
            self::RATING => 'int',
            self::BOOLEAN => 'bool',
            self::DATE_TIME,
            self::DATE => 'string',
            self::SELECT => 'string',
            self::RELATION => 'string', // Relation IDs are strings
            default => 'mixed',
        };
    }
}
