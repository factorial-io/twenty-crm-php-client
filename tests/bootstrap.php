<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set default test mode if not specified
if (!isset($_ENV['TWENTY_TEST_MODE'])) {
    $_ENV['TWENTY_TEST_MODE'] = 'unit';
}
