#!/usr/bin/env php
<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

spl_autoload_register(static function (string $class): void {
    foreach (['helpers', 'middleware', 'controllers', 'models'] as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/app/helpers/Env.php';
Env::load(BASE_PATH . '/.env');

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
