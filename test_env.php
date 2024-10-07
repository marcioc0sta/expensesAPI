<?php
// test_env.php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo 'Loaded .env file: ' . (file_exists(__DIR__ . '/.env') ? 'Yes' : 'No') . PHP_EOL;

echo 'DB_HOST: ' . $_ENV['DB_HOST'] . PHP_EOL;
echo 'DB_PORT: ' . $_ENV['DB_PORT'] . PHP_EOL;
echo 'DB_NAME: ' . $_ENV['DB_NAME'] . PHP_EOL;
echo 'DB_USER: ' . $_ENV['DB_USER'] . PHP_EOL;
echo 'DB_PASS: ' . $_ENV['DB_PASS'] . PHP_EOL;