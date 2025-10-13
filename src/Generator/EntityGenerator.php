<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

use Factorial\TwentyCrm\Entity\DynamicEntity;
use Factorial\TwentyCrm\FieldHandlers\FieldHandlerRegistry;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Metadata\FieldConstants;
use Factorial\TwentyCrm\Metadata\FieldMetadata;
use Factorial\TwentyCrm\Registry\EntityRegistry;
use Factorial\TwentyCrm\Services\MetadataService;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

/**
 * Generates typed entity classes from Twenty CRM metadata.
 *
 * This generator creates PHP classes with typed getters and setters for each
 * entity field, providing IDE autocomplete and static analysis support.
 *
 * Uses Nette PHP Generator for clean, PSR-12 compliant code generation.
 */
class EntityGenerator
{
    private EntityRegistry $registry;
    private PsrPrinter $printer;
    private FieldHandlerRegistry $handlers;
    private ServiceGenerator $serviceGenerator;
    private CollectionGenerator $collectionGenerator;

    public function __construct(
        private readonly CodegenConfig $config,
        private readonly HttpClientInterface $httpClient,
    ) {
        $metadataService = new MetadataService($this->httpClient);
        $this->registry = new EntityRegistry($this->httpClient, $metadataService);
        $this->printer = new PsrPrinter();
        $this->handlers = new FieldHandlerRegistry();
        $this->serviceGenerator = new ServiceGenerator($this->config);
        $this->collectionGenerator = new CollectionGenerator($this->config);
    }

    /**
     * Generate code for a single entity.
     *
     * @param string $entityName Entity name (e.g., 'person', 'company', 'campaign')
     * @param bool $withService Generate service class (default: false)
     * @param bool $withCollection Generate collection class (default: false)
     * @return array<string, string> Map of component type => generated file path
     * @throws \RuntimeException If entity doesn't exist or generation fails
     */
    public function generateEntity(string $entityName, bool $withService = false, bool $withCollection = false): array
    {
        $definition = $this->registry->getDefinition($entityName);

        if (!$definition) {
            throw new \RuntimeException("Entity not found: {$entityName}");
        }

        $this->config->ensureOutputDirectory();
        $this->config->ensureSubdirectories();

        $className = $this->getClassName($entityName);
        $generated = [];

        // Generate entity class
        $entityPath = $this->generateEntityFile($definition, $className);
        $generated['entity'] = $entityPath;

        // Generate collection class if requested
        if ($withCollection) {
            $collectionPath = $this->generateCollectionFile($definition, $className);
            $generated['collection'] = $collectionPath;
        }

        // Generate service class if requested
        if ($withService) {
            $servicePath = $this->generateServiceFile($definition, $className);
            $generated['service'] = $servicePath;
        }

        return $generated;
    }

    /**
     * Generate entity file.
     *
     * @param EntityDefinition $definition
     * @param string $className
     * @return string Path to generated file
     * @throws \RuntimeException If generation fails
     */
    private function generateEntityFile(EntityDefinition $definition, string $className): string
    {
        $filePath = $this->getFilePath($className);

        // Check if file exists and overwrite option
        if (file_exists($filePath) && !$this->config->hasOption('overwrite')) {
            throw new \RuntimeException("File already exists (use --overwrite to replace): {$filePath}");
        }

        $code = $this->generateEntityClass($definition, $className);

        if (file_put_contents($filePath, $code) === false) {
            throw new \RuntimeException("Failed to write file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate collection file.
     *
     * @param EntityDefinition $definition
     * @param string $entityClassName
     * @return string Path to generated file
     * @throws \RuntimeException If generation fails
     */
    private function generateCollectionFile(EntityDefinition $definition, string $entityClassName): string
    {
        $collectionClassName = $entityClassName . 'Collection';
        $filePath = $this->config->getCollectionDir() . '/' . $collectionClassName . '.php';

        // Check if file exists and overwrite option
        if (file_exists($filePath) && !$this->config->hasOption('overwrite')) {
            throw new \RuntimeException("File already exists (use --overwrite to replace): {$filePath}");
        }

        $code = $this->collectionGenerator->generateCollection($definition, $entityClassName);

        if (file_put_contents($filePath, $code) === false) {
            throw new \RuntimeException("Failed to write file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate service file.
     *
     * @param EntityDefinition $definition
     * @param string $entityClassName
     * @return string Path to generated file
     * @throws \RuntimeException If generation fails
     */
    private function generateServiceFile(EntityDefinition $definition, string $entityClassName): string
    {
        $serviceClassName = $entityClassName . 'Service';
        $filePath = $this->config->getServiceDir() . '/' . $serviceClassName . '.php';

        // Check if file exists and overwrite option
        if (file_exists($filePath) && !$this->config->hasOption('overwrite')) {
            throw new \RuntimeException("File already exists (use --overwrite to replace): {$filePath}");
        }

        $code = $this->serviceGenerator->generateService($definition, $entityClassName);

        if (file_put_contents($filePath, $code) === false) {
            throw new \RuntimeException("Failed to write file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate all configured entities.
     *
     * @param bool $withService Generate service classes (default: false)
     * @param bool $withCollection Generate collection classes (default: false)
     * @return array<string, array<string, string>> Map of entity name => [component => path]
     */
    public function generateAll(bool $withService = false, bool $withCollection = false): array
    {
        $generated = [];

        foreach ($this->config->entities as $entityName) {
            $generated[$entityName] = $this->generateEntity($entityName, $withService, $withCollection);
        }

        return $generated;
    }

    /**
     * Generate the entity class code using Nette PHP Generator.
     *
     * @param EntityDefinition $definition
     * @param string $className
     * @return string
     */
    private function generateEntityClass(EntityDefinition $definition, string $className): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($this->config->getEntityNamespace());
        $namespace->addUse('Factorial\TwentyCrm\Entity\StaticEntity');
        $namespace->addUse('Factorial\TwentyCrm\Enums\FieldType');

        // Add use statements for collection types
        foreach ($definition->fields as $field) {
            if ($this->handlers->hasHandler($field->type)) {
                $phpType = $this->handlers->getPhpType($field->type);
                $phpType = ltrim($phpType, '?');
                if (!in_array($phpType, ['mixed', 'string', 'int', 'bool', 'float', 'array'], true)) {
                    $namespace->addUse($phpType);
                }
            }
        }

        $class = $namespace->addClass($className);
        $class->setExtends('Factorial\TwentyCrm\Entity\StaticEntity');
        $class->setComment("{$className} entity (auto-generated).\n\n"
            . "This class provides typed access to {$definition->objectName} entity fields.\n"
            . "Generated from Twenty CRM metadata.\n\n"
            . "All metadata is baked into this class at generation time,\n"
            . "so no runtime API calls are needed.\n\n"
            . "@codingStandardsIgnoreFile\n"
            . "@phpstan-ignore-file");

        // Generate static metadata methods
        $this->addStaticMetadataMethods($class, $definition);

        // Generate getters and setters for each field
        foreach ($definition->fields as $fieldName => $field) {
            // Always generate getters
            $this->addGetter($class, $fieldName, $field);

            // Only generate setters for updatable fields
            // Skip system fields and auto-managed fields (timestamps, audit)
            if (FieldConstants::isUpdatable($field)) {
                $this->addSetter($class, $fieldName, $field);
            }
        }

        return $this->printer->printFile($file);
    }

    /**
     * Add a getter method to the class.
     *
     * Automatically adds use statements for collection types.
     *
     * @param ClassType $class
     * @param string $fieldName
     * @param FieldMetadata $field
     * @return void
     */
    private function addGetter(ClassType $class, string $fieldName, FieldMetadata $field): void
    {
        $methodName = 'get' . $this->toPascalCase($fieldName);
        $phpType = $this->mapFieldTypeToPhp($field);

        // Add use statement for complex types (not primitive types)
        $primitiveTypes = ['mixed', 'string', 'int', 'bool', 'float', 'array'];
        if (!in_array($phpType, $primitiveTypes, true)) {
            $class->getNamespace()->addUse($phpType);
        }

        $method = $class->addMethod($methodName);
        $method->setPublic();
        $method->setReturnType($phpType);
        $method->setReturnNullable($field->isNullable);
        $method->setComment("Get {$fieldName}.");
        $method->setBody("return \$this->get('{$fieldName}');");
    }

    /**
     * Add a setter method to the class.
     *
     * Automatically adds use statements for collection types.
     *
     * @param ClassType $class
     * @param string $fieldName
     * @param FieldMetadata $field
     * @return void
     */
    private function addSetter(ClassType $class, string $fieldName, FieldMetadata $field): void
    {
        $methodName = 'set' . $this->toPascalCase($fieldName);
        $phpType = $this->mapFieldTypeToPhp($field);

        // Add use statement for complex types (not primitive types)
        $primitiveTypes = ['mixed', 'string', 'int', 'bool', 'float', 'array'];
        if (!in_array($phpType, $primitiveTypes, true)) {
            $class->getNamespace()->addUse($phpType);
        }

        $method = $class->addMethod($methodName);
        $method->setPublic();
        $method->setReturnType('self');
        $method->setComment("Set {$fieldName}.");

        $param = $method->addParameter('value');
        $param->setType($phpType);
        $param->setNullable($field->isNullable);

        $method->setBody("\$this->set('{$fieldName}', \$value);\nreturn \$this;");
    }

    /**
     * Map Twenty CRM field type to PHP type.
     *
     * Uses FieldHandlerRegistry to determine types for complex fields.
     * Falls back to basic types for simple fields.
     *
     * @param FieldMetadata $field
     * @return string
     */
    private function mapFieldTypeToPhp(FieldMetadata $field): string
    {
        // Check if we have a handler for this field type
        if ($this->handlers->hasHandler($field->type)) {
            $phpType = $this->handlers->getPhpType($field->type);
            // Remove leading ? if present, we'll handle nullability separately
            return ltrim($phpType, '?');
        }

        // Fall back to FieldType's PHP type mapping
        return $field->type->getPhpType();
    }

    /**
     * Convert field name to PascalCase for method names.
     *
     * @param string $fieldName
     * @return string
     */
    private function toPascalCase(string $fieldName): string
    {
        // Handle camelCase and snake_case
        $fieldName = str_replace('_', ' ', $fieldName);
        $fieldName = ucwords($fieldName);
        return str_replace(' ', '', $fieldName);
    }

    /**
     * Get the class name for an entity.
     *
     * @param string $entityName
     * @return string
     */
    private function getClassName(string $entityName): string
    {
        return $this->toPascalCase($entityName);
    }

    /**
     * Get the file path for a generated entity class.
     *
     * @param string $className
     * @return string
     */
    private function getFilePath(string $className): string
    {
        return $this->config->getEntityDir() . '/' . $className . '.php';
    }

    /**
     * Add static metadata methods to the class.
     *
     * These methods provide all metadata at compile-time, eliminating
     * the need for runtime EntityDefinition.
     *
     * @param \Nette\PhpGenerator\ClassType $class
     * @param EntityDefinition $definition
     * @return void
     */
    private function addStaticMetadataMethods(\Nette\PhpGenerator\ClassType $class, EntityDefinition $definition): void
    {
        // getEntityName()
        $method = $class->addMethod('getEntityName');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('string');
        $method->setBody("return ?;", [$definition->objectName]);

        // getEntityNamePlural()
        $method = $class->addMethod('getEntityNamePlural');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('string');
        $method->setBody("return ?;", [$definition->objectNamePlural]);

        // getApiEndpoint()
        $method = $class->addMethod('getApiEndpoint');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('string');
        $method->setBody("return ?;", [$definition->apiEndpoint]);

        // getAllFieldNames()
        $fieldNames = array_keys($definition->fields);
        $method = $class->addMethod('getAllFieldNames');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('array');
        $method->setBody("return ?;", [$fieldNames]);

        // getFieldMetadata()
        $this->addGetFieldMetadataMethod($class, $definition);

        // getFieldToApiMap()
        $this->addGetFieldToApiMapMethod($class, $definition);

        // getApiToFieldMap()
        $this->addGetApiToFieldMapMethod($class, $definition);
    }

    /**
     * Add getFieldMetadata() method.
     *
     * @param \Nette\PhpGenerator\ClassType $class
     * @param EntityDefinition $definition
     * @return void
     */
    private function addGetFieldMetadataMethod(\Nette\PhpGenerator\ClassType $class, EntityDefinition $definition): void
    {
        $method = $class->addMethod('getFieldMetadata');
        $method->setProtected();
        $method->setStatic();
        $method->addParameter('fieldName')->setType('string');
        $method->setReturnType('?array');

        // Build match expression for field metadata
        $cases = [];
        foreach ($definition->fields as $fieldName => $field) {
            $handlers = $this->handlers;
            $hasHandler = $handlers->hasHandler($field->type);

            $metadata = [
                'type' => new \Nette\PhpGenerator\Literal("FieldType::{$field->type->name}"),
                'nullable' => $field->isNullable,
                'hasHandler' => $hasHandler,
                'isCustom' => $field->isCustom,
                'isSystem' => $field->isSystem,
                'label' => $field->label,
                'description' => $field->description,
                'defaultValue' => $field->defaultValue,
                'objectMetadataId' => $field->objectMetadataId,
                'id' => $field->id,
                'isActive' => $field->isActive,
                'icon' => $field->icon,
            ];

            $cases[] = "'{$fieldName}' => " . $this->arrayToCode($metadata);
        }

        $body = "return match (\$fieldName) {\n    " . implode(",\n    ", $cases) . ",\n    default => null,\n};";
        $method->setBody($body);
    }

    /**
     * Add getFieldToApiMap() method.
     *
     * @param \Nette\PhpGenerator\ClassType $class
     * @param EntityDefinition $definition
     * @return void
     */
    private function addGetFieldToApiMapMethod(\Nette\PhpGenerator\ClassType $class, EntityDefinition $definition): void
    {
        $method = $class->addMethod('getFieldToApiMap');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('array');

        $map = [];
        foreach ($definition->fields as $fieldName => $field) {
            if ($field->type->isRelation()) {
                $map[$fieldName] = $fieldName . 'Id';
            }
        }

        $method->setBody("return ?;", [$map]);
    }

    /**
     * Add getApiToFieldMap() method.
     *
     * @param \Nette\PhpGenerator\ClassType $class
     * @param EntityDefinition $definition
     * @return void
     */
    private function addGetApiToFieldMapMethod(\Nette\PhpGenerator\ClassType $class, EntityDefinition $definition): void
    {
        $method = $class->addMethod('getApiToFieldMap');
        $method->setProtected();
        $method->setStatic();
        $method->setReturnType('array');

        $map = [];
        foreach ($definition->fields as $fieldName => $field) {
            if ($field->type->isRelation()) {
                $map[$fieldName . 'Id'] = $fieldName;
            }
        }

        $method->setBody("return ?;", [$map]);
    }

    /**
     * Convert array to PHP code representation.
     *
     * @param array<string, mixed> $array
     * @return string
     */
    private function arrayToCode(array $array): string
    {
        $parts = [];
        foreach ($array as $key => $value) {
            if ($value === null) {
                $parts[] = "'{$key}' => null";
            } elseif (is_bool($value)) {
                $parts[] = "'{$key}' => " . ($value ? 'true' : 'false');
            } elseif (is_string($value)) {
                $parts[] = "'{$key}' => " . var_export($value, true);
            } elseif ($value instanceof \Nette\PhpGenerator\Literal) {
                $parts[] = "'{$key}' => {$value}";
            } else {
                $parts[] = "'{$key}' => " . var_export($value, true);
            }
        }
        return '[' . implode(', ', $parts) . ']';
    }
}
