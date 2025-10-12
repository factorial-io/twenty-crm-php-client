<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

use Factorial\TwentyCrm\DTO\DynamicEntityCollection;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

/**
 * Generates typed collection classes for entities.
 *
 * Creates collection classes that wrap DynamicEntityCollection with
 * entity-specific types.
 */
class CollectionGenerator
{
    private PsrPrinter $printer;

    public function __construct(
        private readonly CodegenConfig $config,
    ) {
        $this->printer = new PsrPrinter();
    }

    /**
     * Generate collection class for an entity.
     *
     * @param EntityDefinition $definition Entity definition
     * @param string $entityClassName Entity class name (e.g., 'Person')
     * @return string Generated collection code
     */
    public function generateCollection(EntityDefinition $definition, string $entityClassName): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($this->config->getCollectionNamespace());

        // Add use statements
        $namespace->addUse(DynamicEntityCollection::class);
        $namespace->addUse(EntityDefinition::class);

        // Add use for entity class from Entity namespace
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;
        $namespace->addUse($entityFullClass);

        $collectionClassName = $entityClassName . 'Collection';

        $class = $namespace->addClass($collectionClassName);
        $class->setFinal();
        $class->setComment("{$collectionClassName} (auto-generated).\n\n"
            . "Typed collection of {$entityClassName} entities.\n\n"
            . "@codingStandardsIgnoreFile\n"
            . "@phpstan-ignore-file");

        // Add properties
        $class->addProperty('entities')
            ->setPrivate()
            ->setType('array')
            ->addComment("@var {$entityClassName}[]");

        // Add constructor
        $constructor = $class->addMethod('__construct');
        $constructor->addParameter('entities')
            ->setType('array')
            ->addComment("@param {$entityClassName}[] \$entities");
        $constructor->setBody('$this->entities = $entities;');

        // Add fromDynamicCollection static method
        $this->addFromDynamicCollectionMethod($class, $entityClassName);

        // Add getEntities method (generic getter)
        $method = $class->addMethod('getEntities');
        $method->setPublic();
        $method->setReturnType('array');
        $method->addComment("Get all entities in the collection.\n");
        $method->addComment("@return {$entityClassName}[]");
        $method->setBody('return $this->entities;');

        // Add entity-specific getter (e.g., getPersons(), getCompanies())
        $pluralName = $this->getPluralName($entityClassName);
        $method = $class->addMethod('get' . $pluralName);
        $method->setPublic();
        $method->setReturnType('array');
        $method->addComment("Get all {$entityClassName} entities.\n");
        $method->addComment("@return {$entityClassName}[]");
        $method->setBody('return $this->entities;');

        // Add count method
        $method = $class->addMethod('count');
        $method->setPublic();
        $method->setReturnType('int');
        $method->addComment("Get the number of entities in the collection.");
        $method->setBody('return count($this->entities);');

        // Add isEmpty method
        $method = $class->addMethod('isEmpty');
        $method->setPublic();
        $method->setReturnType('bool');
        $method->addComment("Check if the collection is empty.");
        $method->setBody('return empty($this->entities);');

        // Add first method
        $method = $class->addMethod('first');
        $method->setPublic();
        $method->setReturnType($entityFullClass);
        $method->setReturnNullable(true);
        $method->addComment("Get the first entity in the collection.\n");
        $method->addComment("@return {$entityClassName}|null");
        $method->setBody('return $this->entities[0] ?? null;');

        return $this->printer->printFile($file);
    }

    private function addFromDynamicCollectionMethod(ClassType $class, string $entityClassName): void
    {
        $method = $class->addMethod('fromDynamicCollection');
        $method->setPublic();
        $method->setStatic();
        $method->setReturnType('self');
        $method->addComment("Create typed collection from DynamicEntityCollection.\n");
        $method->addComment("@param DynamicEntityCollection \$collection");
        $method->addComment("@return self");

        $method->addParameter('collection')->setType(DynamicEntityCollection::class);

        $method->setBody(
            '$definition = $collection->getDefinition();' . "\n" .
            '$entities = [];' . "\n\n" .
            'foreach ($collection->getEntities() as $dynamicEntity) {' . "\n" .
            '    $entities[] = new ' . $entityClassName . '($definition, $dynamicEntity->toArray());' . "\n" .
            '}' . "\n\n" .
            'return new self($entities);'
        );
    }

    private function getPluralName(string $className): string
    {
        // Simple pluralization (can be enhanced)
        if (str_ends_with($className, 'y')) {
            return substr($className, 0, -1) . 'ies'; // Company -> Companies
        }

        if (str_ends_with($className, 's')) {
            return $className . 'es'; // Business -> Businesses
        }

        return $className . 's'; // Person -> Persons (we can customize specific cases below)
    }
}
