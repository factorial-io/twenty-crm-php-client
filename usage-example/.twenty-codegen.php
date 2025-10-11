<?php

/**
 * Code generation configuration for Factorial's Twenty CRM instance.
 *
 * This file will be used by: vendor/bin/twenty-generate --config=.twenty-codegen.php
 *
 * Usage:
 * 1. Set TWENTY_API_URL and TWENTY_API_TOKEN in .env file
 * 2. Run: vendor/bin/twenty-generate --config=.twenty-codegen.php
 * 3. Generated entities will be in src/ directory
 * 4. Commit generated code to version control
 */

// Load .env file
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return [
    // Target namespace for generated entities
    'namespace' => 'Factorial\\TwentyCrm\\Entities',

    // Output directory for generated code
    'output_dir' => __DIR__ . '/src',

    // Twenty CRM API configuration
    'api_url' => getenv('TWENTY_API_BASE_URI') ?: 'https://factorial.twenty.com/rest/',
    'api_token' => getenv('TWENTY_API_TOKEN'),

    // Entities to generate
    // These match Factorial's Twenty CRM instance schema
    'entities' => [
        'person',      // Standard entity (Contact in v0.x)
        'company',     // Standard entity
        'campaign',    // Custom entity (Factorial-specific)
    ],

    // Code generation options
    'options' => [
        // Generate service classes (PersonService, CompanyService, etc.)
        'generate_services' => true,

        // Generate collection classes (PersonCollection, CompanyCollection, etc.)
        'generate_collections' => true,

        // Generate search filter classes (PersonSearchFilter, etc.)
        'generate_filters' => true,

        // Include PHPStan annotations
        'phpstan_annotations' => true,

        // Include PHPDoc blocks
        'phpdoc' => true,

        // Coding standard to follow
        'coding_standard' => 'PSR-12',
    ],
];
