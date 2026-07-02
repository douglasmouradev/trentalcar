#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap-cli.php';

ProductionGuard::abortIfProduction('database/seed.sql');

$seed = BASE_PATH . '/database/seed.sql';
if (!is_file($seed)) {
    fwrite(STDERR, "Ficheiro não encontrado: {$seed}\n");
    exit(1);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db = $_ENV['DB_DATABASE'] ?? 'titanium_rental_car';

$cmd = sprintf(
    'mysql -h %s -P %s -u%s %s %s < %s',
    escapeshellarg($host),
    escapeshellarg($port),
    escapeshellarg($user),
    $pass !== '' ? '-p' . escapeshellarg($pass) . ' ' : '',
    escapeshellarg($db),
    escapeshellarg($seed)
);

passthru($cmd, $code);
exit($code);
