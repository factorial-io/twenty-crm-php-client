<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Query;

use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\SelectField;

/**
 * Composable filter builder for Twenty CRM API queries.
 *
 * Provides a fluent interface for building type-safe filters with validation.
 *
 * Example:
 * ```php
 * $filter = FilterBuilder::create()
 *     ->where('name.firstName', 'eq', 'John')
 *     ->where('status', 'in', ['ACTIVE', 'PENDING'])
 *     ->where('createdAt', 'gt', '2025-01-01')
 *     ->build();
 * ```
 */
class FilterBuilder implements FilterInterface
{
    /**
     * Filter conditions.
     *
     * @var array<int, array{field: string, operator: string, value: mixed}>
     */
    private array $conditions = [];

    /**
     * Logical operator for combining conditions (AND/OR).
     *
     * @var string
     */
    private string $logicalOperator = 'and';

    /**
     * Entity definition for validation (optional).
     *
     * @var EntityDefinition|null
     */
    private ?EntityDefinition $definition = null;

    /**
     * Create a new filter builder instance.
     *
     * @param EntityDefinition|null $definition Optional entity definition for validation
     */
    public function __construct(?EntityDefinition $definition = null)
    {
        $this->definition = $definition;
    }

    /**
     * Static factory method for fluent interface.
     *
     * @param EntityDefinition|null $definition Optional entity definition
     * @return self
     */
    public static function create(?EntityDefinition $definition = null): self
    {
        return new self($definition);
    }

    /**
     * Static factory method with entity definition.
     *
     * @param EntityDefinition $definition Entity definition for validation
     * @return self
     */
    public static function forEntity(EntityDefinition $definition): self
    {
        return new self($definition);
    }

    /**
     * Add a WHERE condition.
     *
     * @param string $field Field name (supports dot notation: 'name.firstName')
     * @param string $operator Comparison operator (eq, neq, gt, gte, lt, lte, in, contains, etc.)
     * @param mixed $value Value to compare
     * @return self
     * @throws \InvalidArgumentException If validation fails
     */
    public function where(string $field, string $operator, mixed $value): self
    {
        // Validate operator (aligned with Twenty CRM API operators)
        $validOperators = [
            'eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'containsAny', 'is', 'startsWith', 'like', 'ilike'
        ];
        if (!in_array($operator, $validOperators, true)) {
            $validOpsStr = implode(', ', $validOperators);
            throw new \InvalidArgumentException("Invalid operator: {$operator}. Valid operators: {$validOpsStr}");
        }

        // Validate field if definition is provided
        if ($this->definition !== null) {
            $this->validateField($field, $value);
        }

        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add equals condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function equals(string $field, mixed $value): self
    {
        return $this->where($field, 'eq', $value);
    }

    /**
     * Add not equals condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function notEquals(string $field, mixed $value): self
    {
        return $this->where($field, 'neq', $value);
    }

    /**
     * Add greater than condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function greaterThan(string $field, mixed $value): self
    {
        return $this->where($field, 'gt', $value);
    }

    /**
     * Add greater than or equals condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function greaterThanOrEquals(string $field, mixed $value): self
    {
        return $this->where($field, 'gte', $value);
    }

    /**
     * Add less than condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function lessThan(string $field, mixed $value): self
    {
        return $this->where($field, 'lt', $value);
    }

    /**
     * Add less than or equals condition.
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function lessThanOrEquals(string $field, mixed $value): self
    {
        return $this->where($field, 'lte', $value);
    }

    /**
     * Add IN condition (value in array).
     *
     * @param string $field Field name
     * @param array<mixed> $values Array of values
     * @return self
     */
    public function in(string $field, array $values): self
    {
        return $this->where($field, 'in', $values);
    }

    /**
     * Add contains condition (substring search using ILIKE with wildcards).
     *
     * Automatically wraps the value with SQL wildcards (%) for substring matching.
     * Uses case-insensitive ILIKE operator for more forgiving searches.
     * Special LIKE characters (%, _, \) in the value are escaped to match literally.
     *
     * @param string $field Field name
     * @param string $value Substring to search for
     * @return self
     */
    public function contains(string $field, string $value): self
    {
        $escaped = $this->escapeLikeValue($value);
        return $this->where($field, 'ilike', "%{$escaped}%");
    }

    /**
     * Add LIKE condition (case-sensitive pattern matching).
     *
     * @param string $field Field name
     * @param string $value Pattern to match
     * @return self
     */
    public function like(string $field, string $value): self
    {
        return $this->where($field, 'like', $value);
    }

    /**
     * Add ILIKE condition (case-insensitive pattern matching).
     *
     * @param string $field Field name
     * @param string $value Pattern to match
     * @return self
     */
    public function ilike(string $field, string $value): self
    {
        return $this->where($field, 'ilike', $value);
    }

    /**
     * Add starts with condition.
     *
     * @param string $field Field name
     * @param string $value Prefix to match
     * @return self
     */
    public function startsWith(string $field, string $value): self
    {
        return $this->where($field, 'startsWith', $value);
    }

    /**
     * Add IS NULL condition.
     *
     * @param string $field Field name
     * @return self
     */
    public function isNull(string $field): self
    {
        return $this->where($field, 'is', 'NULL');
    }

    /**
     * Add IS NOT NULL condition using neq operator.
     *
     * Note: Twenty CRM API doesn't have a dedicated "IS NOT NULL" operator.
     * This uses neq (not equals) with NULL as a workaround.
     *
     * @param string $field Field name
     * @return self
     */
    public function isNotNull(string $field): self
    {
        return $this->where($field, 'neq', 'NULL');
    }

    /**
     * Set logical operator for combining conditions.
     *
     * @param string $operator 'and' or 'or'
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setLogicalOperator(string $operator): self
    {
        $operator = strtolower($operator);
        if (!in_array($operator, ['and', 'or'], true)) {
            throw new \InvalidArgumentException("Invalid logical operator: {$operator}. Use 'and' or 'or'.");
        }

        $this->logicalOperator = $operator;
        return $this;
    }

    /**
     * Use OR for combining conditions.
     *
     * @return self
     */
    public function useOr(): self
    {
        return $this->setLogicalOperator('or');
    }

    /**
     * Use AND for combining conditions (default).
     *
     * @return self
     */
    public function useAnd(): self
    {
        return $this->setLogicalOperator('and');
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterString(): ?string
    {
        if (empty($this->conditions)) {
            return null;
        }

        $parts = [];
        foreach ($this->conditions as $condition) {
            $parts[] = $this->buildCondition($condition);
        }

        // Twenty CRM uses comma for AND, or(...) for OR
        if ($this->logicalOperator === 'or') {
            return 'or(' . implode(',', $parts) . ')';
        }

        return implode(',', $parts);
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilters(): bool
    {
        return !empty($this->conditions);
    }

    /**
     * Build a CustomFilter from this builder.
     *
     * @return CustomFilter
     */
    public function build(): CustomFilter
    {
        return new CustomFilter($this->buildFilterString());
    }

    /**
     * Build a single condition string.
     *
     * @param array{field: string, operator: string, value: mixed} $condition
     * @return string
     */
    private function buildCondition(array $condition): string
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        // Handle IN operator specially (array values)
        // Format: field[in]:["value1","value2"]
        if ($operator === 'in' && is_array($value)) {
            $valueStr = '[' . implode(',', array_map(fn ($v) => $this->formatValue($v), $value)) . ']';
            return "{$field}[{$operator}]:{$valueStr}";
        }

        // Handle NULL values
        // Format: field[is]:NULL or field[neq]:NULL
        if ($value === 'NULL' && in_array($operator, ['is', 'neq'], true)) {
            return "{$field}[{$operator}]:NULL";
        }

        // Standard condition: field[operator]:value
        $formattedValue = $this->formatValue($value);
        return "{$field}[{$operator}]:{$formattedValue}";
    }

    /**
     * Format a value for the filter string.
     *
     * @param mixed $value
     * @return string
     */
    private function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            // Twenty CRM format: "value" with escaped inner quotes
            $escaped = str_replace('"', '\\"', $value);
            return "\"{$escaped}\"";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'NULL';
        }

        // Numbers, other types
        return (string) $value;
    }

    /**
     * Escape special LIKE wildcard characters in a value.
     *
     * Escapes backslash (\), percent (%), and underscore (_) to prevent
     * SQL injection and ensure they are matched literally in LIKE queries.
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    private function escapeLikeValue(string $value): string
    {
        // Escape in order: backslash first, then wildcards
        // This prevents double-escaping issues
        $value = str_replace('\\', '\\\\', $value); // \ -> \\
        $value = str_replace('%', '\\%', $value);   // % -> \%
        $value = str_replace('_', '\\_', $value);   // _ -> \_
        return $value;
    }

    /**
     * Validate field against entity definition.
     *
     * @param string $field Field name
     * @param mixed $value Value to validate
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateField(string $field, mixed $value): void
    {
        if ($this->definition === null) {
            return;
        }

        // Extract base field name (handle dot notation like 'name.firstName')
        $baseField = explode('.', $field)[0];

        $fieldMeta = $this->definition->getField($baseField);
        if ($fieldMeta === null) {
            $objectName = $this->definition->objectName;
            throw new \InvalidArgumentException("Unknown field: {$baseField} for entity {$objectName}");
        }

        // Validate SELECT field values
        if ($fieldMeta instanceof SelectField && is_string($value)) {
            if (!$fieldMeta->isValidValue($value)) {
                $validValues = implode(', ', $fieldMeta->getValidValues());
                $message = "Invalid value '{$value}' for SELECT field '{$baseField}'. " .
                    "Valid values: {$validValues}";
                throw new \InvalidArgumentException($message);
            }
        }

        // Validate IN operator with SELECT field
        if ($fieldMeta instanceof SelectField && is_array($value)) {
            foreach ($value as $v) {
                if (is_string($v) && !$fieldMeta->isValidValue($v)) {
                    $validValues = implode(', ', $fieldMeta->getValidValues());
                    $message = "Invalid value '{$v}' in array for SELECT field '{$baseField}'. " .
                        "Valid values: {$validValues}";
                    throw new \InvalidArgumentException($message);
                }
            }
        }
    }

    /**
     * Get the entity definition (if set).
     *
     * @return EntityDefinition|null
     */
    public function getDefinition(): ?EntityDefinition
    {
        return $this->definition;
    }

    /**
     * Get all conditions.
     *
     * @return array<int, array{field: string, operator: string, value: mixed}>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Clear all conditions.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->conditions = [];
        return $this;
    }
}
