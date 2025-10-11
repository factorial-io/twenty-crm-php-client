<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

/**
 * Configuration for entity code generation.
 *
 * This class holds all configuration needed to generate typed entity classes
 * from Twenty CRM metadata. Configuration can be loaded from a PHP file or
 * created programmatically.
 */
class CodegenConfig
{
    /**
     * @param string $namespace Target namespace for generated classes
     * @param string $outputDir Output directory for generated files
     * @param string $apiUrl Twenty CRM API base URL
     * @param string $apiToken Twenty CRM API token
     * @param array<string> $entities List of entity names to generate (e.g., ['person', 'company', 'campaign'])
     * @param array<string, mixed> $options Additional options for generation
     */
    public function __construct(
        public readonly string $namespace,
        public readonly string $outputDir,
        public readonly string $apiUrl,
        public readonly string $apiToken,
        public readonly array $entities = [],
        public readonly array $options = [],
    ) {
    }

    /**
     * Load configuration from a PHP file.
     *
     * Expected file format:
     * ```php
     * return [
     *     'namespace' => 'MyApp\TwentyCrm\Entities',
     *     'output_dir' => 'src/TwentyCrm/Entities',
     *     'api_url' => 'https://my-twenty.example.com/rest/',
     *     'api_token' => getenv('TWENTY_API_TOKEN'),
     *     'entities' => ['person', 'company', 'campaign'],
     *     'options' => [
     *         'overwrite' => true,
     *         'generate_services' => true,
     *     ],
     * ];
     * ```
     *
     * @param string $path Path to configuration file
     * @return self
     * @throws \InvalidArgumentException If file doesn't exist or is invalid
     */
    public static function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Config file not found: {$path}");
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new \InvalidArgumentException("Config file must return an array: {$path}");
        }

        $apiToken = $config['api_token'] ?? null;
        if (empty($apiToken)) {
            throw new \InvalidArgumentException(
                'Missing required config: api_token. '
                . 'Please set TWENTY_API_TOKEN environment variable or provide it in config file.'
            );
        }

        return new self(
            namespace: $config['namespace'] ?? throw new \InvalidArgumentException('Missing required config: namespace'),
            outputDir: $config['output_dir'] ?? throw new \InvalidArgumentException('Missing required config: output_dir'),
            apiUrl: $config['api_url'] ?? throw new \InvalidArgumentException('Missing required config: api_url'),
            apiToken: $apiToken,
            entities: $config['entities'] ?? [],
            options: $config['options'] ?? [],
        );
    }

    /**
     * Create configuration from command-line options.
     *
     * Expected options format:
     * - --namespace=MyApp\Entities
     * - --output=src/Entities
     * - --api-url=https://...
     * - --api-token=...
     * - --entities=person,company,campaign
     *
     * @param array<string, mixed> $options Command-line options from getopt()
     * @return self
     * @throws \InvalidArgumentException If required options are missing
     */
    public static function fromOptions(array $options): self
    {
        // Parse entities list
        $entities = [];
        if (isset($options['entities'])) {
            $entities = is_array($options['entities'])
                ? $options['entities']
                : explode(',', $options['entities']);
        } elseif (isset($options['entity'])) {
            $entities = is_array($options['entity'])
                ? $options['entity']
                : [$options['entity']];
        }

        return new self(
            namespace: $options['namespace'] ?? throw new \InvalidArgumentException('Missing required option: --namespace'),
            outputDir: $options['output'] ?? throw new \InvalidArgumentException('Missing required option: --output'),
            apiUrl: $options['api-url'] ?? throw new \InvalidArgumentException('Missing required option: --api-url'),
            apiToken: $options['api-token'] ?? throw new \InvalidArgumentException('Missing required option: --api-token'),
            entities: $entities,
            options: [
                'overwrite' => isset($options['overwrite']),
                'generate_services' => !isset($options['no-services']),
                'generate_collections' => !isset($options['no-collections']),
            ],
        );
    }

    /**
     * Validate the configuration.
     *
     * @return bool True if configuration is valid
     */
    public function validate(): bool
    {
        if (empty($this->namespace)) {
            return false;
        }

        if (empty($this->outputDir)) {
            return false;
        }

        if (empty($this->apiUrl)) {
            return false;
        }

        if (empty($this->apiToken)) {
            return false;
        }

        // Validate namespace format
        if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/', $this->namespace)) {
            return false;
        }

        return true;
    }

    /**
     * Get the option value, with optional default.
     *
     * @param string $key Option key
     * @param mixed $default Default value if option not set
     * @return mixed
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Check if an option is set and true.
     *
     * @param string $key Option key
     * @return bool
     */
    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]) && $this->options[$key];
    }

    /**
     * Get the absolute output directory path.
     *
     * @return string
     */
    public function getAbsoluteOutputDir(): string
    {
        if (str_starts_with($this->outputDir, '/')) {
            return $this->outputDir;
        }

        // Relative to current working directory
        return getcwd() . '/' . $this->outputDir;
    }

    /**
     * Ensure the output directory exists.
     *
     * @return void
     * @throws \RuntimeException If directory cannot be created
     */
    public function ensureOutputDirectory(): void
    {
        $dir = $this->getAbsoluteOutputDir();

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Failed to create output directory: {$dir}");
            }
        }

        if (!is_writable($dir)) {
            throw new \RuntimeException("Output directory is not writable: {$dir}");
        }
    }
}
