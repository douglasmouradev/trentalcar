#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

spl_autoload_register(static function (string $class): void {
    foreach (['helpers', 'middleware', 'controllers', 'models', 'services'] as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/app/helpers/Env.php';
Env::load(BASE_PATH . '/.env');

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db = $_ENV['DB_DATABASE'] ?? 'titanium_rental_car';

$outDir = BASE_PATH . '/storage/backups';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$stamp = date('Ymd-His');
$sqlFile = $outDir . '/db-' . $stamp . '.sql.gz';

$passArg = $pass !== '' ? '-p' . escapeshellarg($pass) : '';
$cmd = sprintf(
    'mysqldump -h %s -P %s -u%s %s %s --single-transaction --routines --triggers 2>/dev/null | gzip > %s',
    escapeshellarg($host),
    escapeshellarg($port),
    escapeshellarg($user),
    $passArg,
    escapeshellarg($db),
    escapeshellarg($sqlFile)
);

if (PHP_OS_FAMILY === 'Windows') {
    $rawFile = $outDir . '/db-' . $stamp . '.sql';
    $cmdWin = sprintf(
        'mysqldump -h %s -P %s -u%s %s %s --single-transaction > %s',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($user),
        $passArg,
        escapeshellarg($db),
        escapeshellarg($rawFile)
    );
    passthru($cmdWin, $code);
    if ($code !== 0 || !is_file($rawFile)) {
        fwrite(STDERR, "Backup falhou (code {$code})\n");
        exit(1);
    }
    $sqlFile = $rawFile;
} else {
    passthru($cmd, $code);
    if ($code !== 0 || !is_file($sqlFile)) {
        fwrite(STDERR, "Backup falhou (code {$code})\n");
        exit(1);
    }
}

$archives = glob($outDir . '/db-*') ?: [];
usort($archives, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
foreach (array_slice($archives, 7) as $stale) {
    @unlink($stale);
}

echo json_encode([
    'file' => basename($sqlFile),
    'size' => filesize($sqlFile),
    'kept' => min(count($archives), 7),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
