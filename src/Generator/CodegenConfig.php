<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Generator;

/**
 * Configuration for entity code generation.
 *
 * This class holds all configuration needed to generate typed entity classes
 * from Twenty CRM metadata. Configuration can be loaded from a YAML file or
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
     * Load configuration from a YAML file.
     *
     * YAML format:
     * ```yaml
     * namespace: MyApp\TwentyCrm
     * output_dir: src/TwentyCrm
     * api_url: https://my-twenty.example.com/rest/
     * api_token: ${TWENTY_API_TOKEN}
     * entities:
     *   - person
     *   - company
     *   - campaign
     * options:
     *   overwrite: true
     *   generate_services: true
     *   generate_collections: true
     * ```
     *
     * @param string $path Path to YAML configuration file (.yaml or .yml)
     * @return self
     * @throws \InvalidArgumentException If file doesn't exist or is invalid
     */
    public static function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Config file not found: {$path}");
        }

        // Check file extension
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!in_array($extension, ['yaml', 'yml'], true)) {
            throw new \InvalidArgumentException(
                "Unsupported config file format: {$extension}. Only .yaml and .yml files are supported."
            );
        }

        if (!class_exists(\Symfony\Component\Yaml\Yaml::class)) {
            throw new \RuntimeException(
                'Symfony YAML component is required. '
                . 'Install it with: composer require symfony/yaml'
            );
        }

        $config = \Symfony\Component\Yaml\Yaml::parseFile($path);

        if (!is_array($config)) {
            throw new \InvalidArgumentException("YAML config file must contain a mapping: {$path}");
        }

        // Process environment variable substitution (${VAR_NAME})
        $config = self::processEnvironmentVariables($config);

        return self::fromArray($config);
    }

    /**
     * Create configuration from an array.
     *
     * @param array<string, mixed> $config Configuration array
     * @return self
     * @throws \InvalidArgumentException If required config is missing
     */
    private static function fromArray(array $config): self
    {
        $apiToken = $config['api_token'] ?? null;
        if (empty($apiToken)) {
            throw new \InvalidArgumentException(
                'Missing required config: api_token. '
                . 'Please set TWENTY_API_TOKEN environment variable or provide it in config file.'
            );
        }

        $namespace = $config['namespace'] ?? throw new \InvalidArgumentException(
            'Missing required config: namespace'
        );
        $outputDir = $config['output_dir'] ?? throw new \InvalidArgumentException(
            'Missing required config: output_dir'
        );
        $apiUrl = $config['api_url'] ?? throw new \InvalidArgumentException(
            'Missing required config: api_url'
        );

        return new self(
            namespace: $namespace,
            outputDir: $outputDir,
            apiUrl: $apiUrl,
            apiToken: $apiToken,
            entities: $config['entities'] ?? [],
            options: $config['options'] ?? [],
        );
    }

    /**
     * Process environment variable substitution in config values.
     *
     * Replaces ${VAR_NAME} with the value of the environment variable.
     *
     * @param array<string, mixed> $config Configuration array
     * @return array<string, mixed> Processed configuration
     */
    private static function processEnvironmentVariables(array $config): array
    {
        array_walk_recursive($config, function (&$value) {
            if (is_string($value) && preg_match('/^\$\{([A-Z_][A-Z0-9_]*)\}$/', $value, $matches)) {
                $envVar = $matches[1];
                $envValue = getenv($envVar);
                $value = $envValue !== false ? $envValue : $value;
            }
        });

        return $config;
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

        $namespace = $options['namespace'] ?? throw new \InvalidArgumentException(
            'Missing required option: --namespace'
        );
        $outputDir = $options['output'] ?? throw new \InvalidArgumentException(
            'Missing required option: --output'
        );
        $apiUrl = $options['api-url'] ?? throw new \InvalidArgumentException(
            'Missing required option: --api-url'
        );
        $apiToken = $options['api-token'] ?? throw new \InvalidArgumentException(
            'Missing required option: --api-token'
        );

        return new self(
            namespace: $namespace,
            outputDir: $outputDir,
            apiUrl: $apiUrl,
            apiToken: $apiToken,
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

        // Validate namespace format (PSR-4 compliant)
        $namespacePattern = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*' .
            '(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/';
        if (!preg_match($namespacePattern, $this->namespace)) {
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

    /**
     * Get the entity subdirectory path.
     *
     * @return string
     */
    public function getEntityDir(): string
    {
        return $this->getAbsoluteOutputDir() . '/Entity';
    }

    /**
     * Get the service subdirectory path.
     *
     * @return string
     */
    public function getServiceDir(): string
    {
        return $this->getAbsoluteOutputDir() . '/Service';
    }

    /**
     * Get the collection subdirectory path.
     *
     * @return string
     */
    public function getCollectionDir(): string
    {
        return $this->getAbsoluteOutputDir() . '/Collection';
    }

    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace(): string
    {
        return $this->namespace . '\\Entity';
    }

    /**
     * Get the service namespace.
     *
     * @return string
     */
    public function getServiceNamespace(): string
    {
        return $this->namespace . '\\Service';
    }

    /**
     * Get the collection namespace.
     *
     * @return string
     */
    public function getCollectionNamespace(): string
    {
        return $this->namespace . '\\Collection';
    }

    /**
     * Ensure all subdirectories exist.
     *
     * @return void
     * @throws \RuntimeException If directories cannot be created
     */
    public function ensureSubdirectories(): void
    {
        $directories = [
            $this->getEntityDir(),
            $this->getServiceDir(),
            $this->getCollectionDir(),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException("Failed to create directory: {$dir}");
                }
            }

            if (!is_writable($dir)) {
                throw new \RuntimeException("Directory is not writable: {$dir}");
            }
        }
    }
}
