<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

use Factorial\TwentyCrm\DTO\DynamicEntity;
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

    public function __construct(
        private readonly CodegenConfig $config,
        private readonly HttpClientInterface $httpClient,
    ) {
        $metadataService = new MetadataService($this->httpClient);
        $this->registry = new EntityRegistry($this->httpClient, $metadataService);
        $this->printer = new PsrPrinter();
    }

    /**
     * Generate code for a single entity.
     *
     * @param string $entityName Entity name (e.g., 'person', 'company', 'campaign')
     * @return string Path to generated file
     * @throws \RuntimeException If entity doesn't exist or generation fails
     */
    public function generateEntity(string $entityName): string
    {
        $definition = $this->registry->getDefinition($entityName);

        if (!$definition) {
            throw new \RuntimeException("Entity not found: {$entityName}");
        }

        $this->config->ensureOutputDirectory();

        $className = $this->getClassName($entityName);
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
     * Generate all configured entities.
     *
     * @return array<string, string> Map of entity name => generated file path
     */
    public function generateAll(): array
    {
        $generated = [];

        foreach ($this->config->entities as $entityName) {
            $generated[$entityName] = $this->generateEntity($entityName);
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

        $namespace = $file->addNamespace($this->config->namespace);
        $namespace->addUse(DynamicEntity::class);
        $namespace->addUse(EntityDefinition::class);

        $class = $namespace->addClass($className);
        $class->setExtends(DynamicEntity::class);
        $class->setComment("{$className} entity (auto-generated).\n\n"
            . "This class provides typed access to {$definition->objectName} entity fields.\n"
            . "Generated from Twenty CRM metadata.\n\n"
            . "@codingStandardsIgnoreFile\n"
            . "@phpstan-ignore-file");

        // Add constructor
        $constructor = $class->addMethod('__construct');
        $constructor->addParameter('definition')->setType(EntityDefinition::class);
        $constructor->addParameter('data')->setType('array')->setDefaultValue([]);
        $constructor->setBody('parent::__construct($definition, $data);');

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
     * @param ClassType $class
     * @param string $fieldName
     * @param FieldMetadata $field
     * @return void
     */
    private function addGetter(ClassType $class, string $fieldName, FieldMetadata $field): void
    {
        $methodName = 'get' . $this->toPascalCase($fieldName);
        $phpType = $this->mapFieldTypeToPhp($field);

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
     * @param ClassType $class
     * @param string $fieldName
     * @param FieldMetadata $field
     * @return void
     */
    private function addSetter(ClassType $class, string $fieldName, FieldMetadata $field): void
    {
        $methodName = 'set' . $this->toPascalCase($fieldName);
        $phpType = $this->mapFieldTypeToPhp($field);

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
     * @param FieldMetadata $field
     * @return string
     */
    private function mapFieldTypeToPhp(FieldMetadata $field): string
    {
        return match ($field->type) {
            'TEXT', 'EMAIL', 'PHONE', 'UUID' => 'string',
            'NUMBER', 'RATING' => 'int',
            'BOOLEAN' => 'bool',
            'DATE_TIME' => 'string', // Could be \DateTimeInterface but API returns string
            'SELECT' => 'string', // Enum values are strings
            'CURRENCY' => 'float',
            'RELATION' => 'mixed', // Relations are complex, use mixed for now
            'LINKS' => 'array',
            'PHONES' => 'array',
            'ADDRESS' => 'array',
            'FULL_NAME' => 'array',
            default => 'mixed',
        };
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
     * Get the file path for a generated class.
     *
     * @param string $className
     * @return string
     */
    private function getFilePath(string $className): string
    {
        return $this->config->getAbsoluteOutputDir() . '/' . $className . '.php';
    }
}
