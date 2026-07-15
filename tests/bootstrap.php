<?php

require __DIR__ . '/../vendor/autoload.php';

$env = static function (string $key, ?string $default = null): ?string {
    return $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
};

$connection = $env('DB_CONNECTION', 'sqlite');
$database = $env('DB_DATABASE');

if ($connection === 'mysql' && $database !== null) {
    if (! preg_match('/(?:_test|_testing)$/', $database)) {
        throw new RuntimeException("Refusing to run tests against non-testing database [{$database}].");
    }

    $host = $env('DB_HOST', '127.0.0.1');
    $port = $env('DB_PORT', '3307');
    $username = $env('DB_USERNAME', 'root');
    $password = $env('DB_PASSWORD', '');
    $charset = $env('DB_CHARSET', 'utf8mb4');

    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset={$charset}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );

    $quotedDatabase = str_replace('`', '``', $database);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$quotedDatabase}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
}