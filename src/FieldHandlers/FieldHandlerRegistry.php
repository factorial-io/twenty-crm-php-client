<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\FieldHandlers;

/**
 * Registry for managing field handlers.
 *
 * Provides lookup of handlers by field type and manages the
 * collection of available handlers for code generation.
 */
class FieldHandlerRegistry
{
    /**
     * Registered handlers indexed by field type.
     *
     * @var array<string, NestedObjectHandler>
     */
    private array $handlers = [];

    /**
     * FieldHandlerRegistry constructor.
     */
    public function __construct()
    {
        // Register default handlers
        $this->registerDefaultHandlers();
    }

    /**
     * Register a field handler.
     *
     * @param NestedObjectHandler $handler The handler to register
     * @return void
     */
    public function register(NestedObjectHandler $handler): void
    {
        $this->handlers[$handler->getFieldType()] = $handler;
    }

    /**
     * Get a handler for a specific field type.
     *
     * @param string $fieldType The Twenty CRM field type (e.g., 'PHONES', 'LINKS')
     * @return NestedObjectHandler|null The handler or null if not found
     */
    public function getHandler(string $fieldType): ?NestedObjectHandler
    {
        return $this->handlers[$fieldType] ?? null;
    }

    /**
     * Check if a handler exists for a field type.
     *
     * @param string $fieldType The field type
     * @return bool True if handler exists
     */
    public function hasHandler(string $fieldType): bool
    {
        return isset($this->handlers[$fieldType]);
    }

    /**
     * Get all registered handlers.
     *
     * @return array<string, NestedObjectHandler> Array of handlers indexed by field type
     */
    public function getAllHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Get the PHP type for a field type.
     *
     * Returns the handler's PHP type if available, otherwise 'mixed'.
     *
     * @param string $fieldType The Twenty CRM field type
     * @return string The PHP type (e.g., 'PhoneCollection', 'string', 'mixed')
     */
    public function getPhpType(string $fieldType): string
    {
        $handler = $this->getHandler($fieldType);
        return $handler ? $handler->getPhpType() : 'mixed';
    }

    /**
     * Transform API data to PHP object.
     *
     * @param string $fieldType The field type
     * @param array $data The API data
     * @return mixed The transformed PHP object or original data
     */
    public function fromApi(string $fieldType, array $data): mixed
    {
        $handler = $this->getHandler($fieldType);
        return $handler ? $handler->fromApi($data) : $data;
    }

    /**
     * Transform PHP object to API format.
     *
     * @param string $fieldType The field type
     * @param mixed $value The PHP object
     * @return array The API format data
     */
    public function toApi(string $fieldType, mixed $value): array
    {
        $handler = $this->getHandler($fieldType);

        if (!$handler) {
            // No handler, pass through if array
            return is_array($value) ? $value : [];
        }

        return $handler->toApi($value);
    }

    /**
     * Register default field handlers.
     *
     * @return void
     */
    private function registerDefaultHandlers(): void
    {
        $this->register(new PhonesFieldHandler());
        $this->register(new LinksFieldHandler());
        $this->register(new EmailsFieldHandler());
        $this->register(new NameFieldHandler());
        $this->register(new AddressFieldHandler());
    }
}
