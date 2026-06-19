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

$dir = BASE_PATH . '/storage/logs';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$rotated = 0;
foreach (['app.log', 'app.jsonl'] as $name) {
    $file = $dir . '/' . $name;
    if (is_file($file) && filesize($file) > 5_000_000) {
        rename($file, $file . '.' . date('Ymd-His') . '.bak');
        $rotated++;
    }
}

$old = glob($dir . '/*.bak') ?: [];
usort($old, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
foreach (array_slice($old, 14) as $stale) {
    @unlink($stale);
}

echo json_encode(['rotated' => $rotated, 'backups_kept' => min(count($old), 14)], JSON_PRETTY_PRINT) . PHP_EOL;
