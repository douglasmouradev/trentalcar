#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

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
