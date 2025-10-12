<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

use Factorial\TwentyCrm\Query\FilterInterface;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\HttpClientInterface;
use Factorial\TwentyCrm\Metadata\EntityDefinition;
use Factorial\TwentyCrm\Services\GenericEntityService;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

/**
 * Generates typed service classes for entities.
 *
 * Creates service classes that wrap GenericEntityService with
 * entity-specific types, providing the same API as ContactService/CompanyService
 * but working with generated entities.
 */
class ServiceGenerator
{
    private PsrPrinter $printer;

    public function __construct(
        private readonly CodegenConfig $config,
    ) {
        $this->printer = new PsrPrinter();
    }

    /**
     * Generate service class for an entity.
     *
     * @param EntityDefinition $definition Entity definition
     * @param string $entityClassName Entity class name (e.g., 'Person')
     * @return string Generated service code
     */
    public function generateService(EntityDefinition $definition, string $entityClassName): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($this->config->getServiceNamespace());

        // Add use statements
        $namespace->addUse(FilterInterface::class);
        $namespace->addUse(SearchOptions::class);
        $namespace->addUse(ApiException::class);
        $namespace->addUse(HttpClientInterface::class);
        $namespace->addUse(EntityDefinition::class);
        $namespace->addUse(GenericEntityService::class);

        // Add use for entity and collection classes from other namespaces
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;
        $collectionFullClass = $this->config->getCollectionNamespace() . '\\' . $entityClassName . 'Collection';
        $namespace->addUse($entityFullClass);
        $namespace->addUse($collectionFullClass);

        $serviceClassName = $entityClassName . 'Service';
        $collectionClassName = $entityClassName . 'Collection';

        $class = $namespace->addClass($serviceClassName);
        $class->setFinal();
        $class->setComment("{$serviceClassName} (auto-generated).\n\n"
            . "Provides typed access to {$entityClassName} CRUD operations.\n"
            . "Wraps GenericEntityService with entity-specific types.\n\n"
            . "@codingStandardsIgnoreFile\n"
            . "@phpstan-ignore-file");

        // Add properties
        $class->addProperty('genericService')
            ->setPrivate()
            ->setReadOnly()
            ->setType(GenericEntityService::class);

        $class->addProperty('definition')
            ->setPrivate()
            ->setReadOnly()
            ->setType(EntityDefinition::class);

        // Add constructor
        $constructor = $class->addMethod('__construct');
        $constructor->addParameter('httpClient')->setType(HttpClientInterface::class);
        $constructor->addParameter('definition')->setType(EntityDefinition::class);
        $constructor->setBody(
            '$this->definition = $definition;' . "\n" .
            '$this->genericService = new GenericEntityService($httpClient, $definition);'
        );

        // Add createInstance() method
        $this->addCreateInstanceMethod($class, $entityClassName);

        // Add find() method
        $this->addFindMethod($class, $entityClassName, $collectionClassName);

        // Add getById() method
        $this->addGetByIdMethod($class, $entityClassName);

        // Add create() method
        $this->addCreateMethod($class, $entityClassName);

        // Add update() method
        $this->addUpdateMethod($class, $entityClassName);

        // Add delete() method
        $this->addDeleteMethod($class);

        // Add batchUpsert() method
        $this->addBatchUpsertMethod($class, $entityClassName, $collectionClassName);

        return $this->printer->printFile($file);
    }

    private function addCreateInstanceMethod(ClassType $class, string $entityClassName): void
    {
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;

        $method = $class->addMethod('createInstance');
        $method->setPublic();
        $method->setReturnType($entityFullClass);
        $method->addComment("Create a new {$entityClassName} instance.\n");
        $method->addComment("@param array \$data Optional initial data for the entity");
        $method->addComment("@return {$entityClassName}");

        $method->addParameter('data')
            ->setType('array')
            ->setDefaultValue([]);

        $method->setBody('return new ' . $entityClassName . '($this->definition, $data);');
    }

    private function addFindMethod(ClassType $class, string $entityClassName, string $collectionClassName): void
    {
        $collectionFullClass = $this->config->getCollectionNamespace() . '\\' . $collectionClassName;

        $method = $class->addMethod('find');
        $method->setPublic();
        $method->setReturnType($collectionFullClass);
        $method->addComment("Find {$entityClassName} entities matching filter.\n");
        $method->addComment("@param FilterInterface \$filter Search filter");
        $method->addComment("@param SearchOptions \$options Search options");
        $method->addComment("@return {$collectionClassName}");

        $method->addParameter('filter')->setType(FilterInterface::class);
        $method->addParameter('options')->setType(SearchOptions::class);

        $method->setBody(
            '$dynamicCollection = $this->genericService->find($filter, $options);' . "\n" .
            'return ' . $collectionClassName . '::fromDynamicCollection($dynamicCollection);'
        );
    }

    private function addGetByIdMethod(ClassType $class, string $entityClassName): void
    {
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;

        $method = $class->addMethod('getById');
        $method->setPublic();
        $method->setReturnType($entityFullClass);
        $method->setReturnNullable(true);
        $method->addComment("Get {$entityClassName} by ID.\n");
        $method->addComment("@param string \$id Entity ID");
        $method->addComment("@return {$entityClassName}|null");

        $method->addParameter('id')->setType('string');

        $method->setBody(
            '$entity = $this->genericService->getById($id);' . "\n\n" .
            'if ($entity === null) {' . "\n" .
            '    return null;' . "\n" .
            '}' . "\n\n" .
            'return new ' . $entityClassName . '($this->definition, $entity->toArray());'
        );
    }

    private function addCreateMethod(ClassType $class, string $entityClassName): void
    {
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;

        $method = $class->addMethod('create');
        $method->setPublic();
        $method->setReturnType($entityFullClass);
        $method->addComment("Create a new {$entityClassName}.\n");
        $method->addComment("@param {$entityClassName} \$entity Entity to create");
        $method->addComment("@return {$entityClassName}");

        $method->addParameter('entity')->setType($entityFullClass);

        $method->setBody(
            '$created = $this->genericService->create($entity);' . "\n" .
            'return new ' . $entityClassName . '($this->definition, $created->toArray());'
        );
    }

    private function addUpdateMethod(ClassType $class, string $entityClassName): void
    {
        $entityFullClass = $this->config->getEntityNamespace() . '\\' . $entityClassName;

        $method = $class->addMethod('update');
        $method->setPublic();
        $method->setReturnType($entityFullClass);
        $method->addComment("Update an existing {$entityClassName}.\n");
        $method->addComment("@param {$entityClassName} \$entity Entity to update");
        $method->addComment("@return {$entityClassName}");

        $method->addParameter('entity')->setType($entityFullClass);

        $method->setBody(
            '$updated = $this->genericService->update($entity);' . "\n" .
            'return new ' . $entityClassName . '($this->definition, $updated->toArray());'
        );
    }

    private function addDeleteMethod(ClassType $class): void
    {
        $method = $class->addMethod('delete');
        $method->setPublic();
        $method->setReturnType('bool');
        $method->addComment("Delete an entity by ID.\n");
        $method->addComment("@param string \$id Entity ID");
        $method->addComment("@return bool True if deleted, false if not found");

        $method->addParameter('id')->setType('string');

        $method->setBody('return $this->genericService->delete($id);');
    }

    private function addBatchUpsertMethod(ClassType $class, string $entityClassName, string $collectionClassName): void
    {
        $collectionFullClass = $this->config->getCollectionNamespace() . '\\' . $collectionClassName;

        $method = $class->addMethod('batchUpsert');
        $method->setPublic();
        $method->setReturnType($collectionFullClass);
        $method->addComment("Batch upsert multiple {$entityClassName} entities.\n");
        $method->addComment("@param {$entityClassName}[] \$entities Entities to upsert");
        $method->addComment("@return {$collectionClassName}");

        $method->addParameter('entities')->setType('array');

        $method->setBody(
            '$dynamicCollection = $this->genericService->batchUpsert($entities);' . "\n" .
            'return ' . $collectionClassName . '::fromDynamicCollection($dynamicCollection);'
        );
    }
}
