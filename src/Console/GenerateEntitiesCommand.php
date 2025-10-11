<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Console;

use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\Generator\CodegenConfig;
use Factorial\TwentyCrm\Generator\EntityGenerator;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate typed entity classes from Twenty CRM metadata.
 *
 * Usage:
 *   bin/twenty-generate --config=.twenty-codegen.php
 *
 *   bin/twenty-generate \
 *     --namespace="MyApp\Entities" \
 *     --output=src/Entities \
 *     --api-url=https://... \
 *     --api-token=... \
 *     --entities=person,company,campaign
 */
class GenerateEntitiesCommand extends Command
{
    protected static $defaultName = 'generate:entities';
    protected static $defaultDescription = 'Generate typed entity classes from Twenty CRM metadata';

    protected function configure(): void
    {
        $this
            ->setName('generate:entities')
            ->setDescription('Generate typed entity classes from Twenty CRM metadata')
            ->setHelp(<<<'HELP'
The <info>generate:entities</info> command generates typed PHP entity classes from your Twenty CRM instance metadata.

<comment>Using a configuration file:</comment>
  <info>php bin/twenty-generate --config=.twenty-codegen.php</info>

<comment>Using command-line options:</comment>
  <info>php bin/twenty-generate \
    --namespace="MyApp\Entities" \
    --output=src/Entities \
    --api-url=https://my-twenty.example.com/rest/ \
    --api-token=your_token \
    --entities=person,company,campaign</info>

<comment>Generate all entities from your Twenty instance:</comment>
  <info>php bin/twenty-generate \
    --namespace="MyApp\Entities" \
    --output=src/Entities \
    --api-url=https://my-twenty.example.com/rest/ \
    --api-token=your_token \
    --all</info>
HELP
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to configuration file (.twenty-codegen.php)'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Target namespace for generated classes (e.g., "MyApp\Entities")'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output directory for generated files (e.g., "src/Entities")'
            )
            ->addOption(
                'api-url',
                null,
                InputOption::VALUE_REQUIRED,
                'Twenty CRM API base URL (e.g., "https://my-twenty.example.com/rest/")'
            )
            ->addOption(
                'api-token',
                null,
                InputOption::VALUE_REQUIRED,
                'Twenty CRM API token'
            )
            ->addOption(
                'entities',
                'e',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of entity names to generate (e.g., "person,company,campaign")'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Generate all entities from your Twenty instance'
            )
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Overwrite existing files'
            )
            ->addOption(
                'no-services',
                null,
                InputOption::VALUE_NONE,
                'Skip generating service classes'
            )
            ->addOption(
                'no-collections',
                null,
                InputOption::VALUE_NONE,
                'Skip generating collection classes'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Twenty CRM Entity Generator');

        try {
            // Load configuration
            $config = $this->loadConfiguration($input, $io);

            // Validate configuration
            if (!$config->validate()) {
                $io->error('Invalid configuration. Please check your settings.');
                return Command::FAILURE;
            }

            // Display configuration summary
            $this->displayConfiguration($config, $io);

            // Create HTTP client
            $httpClient = $this->createHttpClient($config);

            // Create generator
            $generator = new EntityGenerator($config, $httpClient);

            // Generate entities
            $io->section('Generating entities');

            if (empty($config->entities)) {
                $io->warning('No entities specified. Use --entities or --all option.');
                return Command::FAILURE;
            }

            $generatedFiles = [];
            foreach ($config->entities as $entityName) {
                $io->write("  Generating <info>{$entityName}</info>... ");

                try {
                    $filePath = $generator->generateEntity($entityName);
                    $generatedFiles[] = $filePath;
                    $io->writeln('<fg=green>✓</>');
                } catch (\Exception $e) {
                    $io->writeln('<fg=red>✗</>');
                    $io->error("Failed to generate {$entityName}: " . $e->getMessage());
                    return Command::FAILURE;
                }
            }

            // Success message
            $io->success(sprintf(
                'Generated %d %s successfully!',
                count($generatedFiles),
                count($generatedFiles) === 1 ? 'entity' : 'entities'
            ));

            // List generated files
            $io->section('Generated files');
            foreach ($generatedFiles as $file) {
                $io->writeln("  <comment>{$file}</comment>");
            }

            // Next steps
            $io->section('Next steps');
            $io->listing([
                'Review the generated files',
                'Commit them to your repository: <info>git add ' . $config->outputDir . '</info>',
                'Use them in your code with full IDE autocomplete!',
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Generation failed: ' . $e->getMessage());
            if ($output->isVerbose()) {
                $io->writeln($e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    /**
     * Load configuration from file or command-line options.
     */
    private function loadConfiguration(InputInterface $input, SymfonyStyle $io): CodegenConfig
    {
        $configFile = $input->getOption('config');

        if ($configFile) {
            $io->writeln("Loading configuration from: <comment>{$configFile}</comment>");

            // Load .env file if it exists in the same directory as config file
            $configDir = realpath(dirname($configFile)) ?: dirname($configFile);
            $this->loadEnvFile($configDir);

            return CodegenConfig::fromFile($configFile);
        }

        // Build from command-line options
        $options = [
            'namespace' => $input->getOption('namespace'),
            'output' => $input->getOption('output'),
            'api-url' => $input->getOption('api-url'),
            'api-token' => $input->getOption('api-token'),
            'entities' => $input->getOption('entities'),
            'overwrite' => $input->getOption('overwrite'),
            'no-services' => $input->getOption('no-services'),
            'no-collections' => $input->getOption('no-collections'),
        ];

        return CodegenConfig::fromOptions($options);
    }

    /**
     * Load .env file if it exists.
     */
    private function loadEnvFile(string $directory): void
    {
        $envFile = $directory . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        // Check if Dotenv is available
        if (!class_exists(\Dotenv\Dotenv::class)) {
            return;
        }

        try {
            $dotenv = \Dotenv\Dotenv::createImmutable($directory);
            $dotenv->load();
        } catch (\Exception $e) {
            // Silently ignore - .env loading is optional
        }
    }

    /**
     * Display configuration summary.
     */
    private function displayConfiguration(CodegenConfig $config, SymfonyStyle $io): void
    {
        $io->section('Configuration');
        $io->definitionList(
            ['Namespace' => $config->namespace],
            ['Output Directory' => $config->outputDir],
            ['API URL' => $config->apiUrl],
            ['API Token' => str_repeat('*', min(20, strlen($config->apiToken)))],
            ['Entities' => implode(', ', $config->entities)],
            ['Overwrite' => $config->hasOption('overwrite') ? 'Yes' : 'No'],
        );
    }

    /**
     * Create HTTP client for Twenty CRM API.
     */
    private function createHttpClient(CodegenConfig $config): GuzzleHttpClient
    {
        $guzzle = new Client(['timeout' => 30]);

        // Create factories - we need PSR-17 implementations
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $auth = new BearerTokenAuth($config->apiToken);

        return new GuzzleHttpClient(
            $guzzle,
            $requestFactory,
            $streamFactory,
            $auth,
            $config->apiUrl
        );
    }
}
