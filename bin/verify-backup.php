#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

$dir = BASE_PATH . '/storage/backups';
$files = glob($dir . '/db-*') ?: [];
if ($files === []) {
    fwrite(STDERR, "Nenhum backup em storage/backups/\n");
    exit(1);
}
usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
$latest = $files[0];
$size = filesize($latest);
$minBytes = 100;
if ($size === false || $size < $minBytes) {
    fwrite(STDERR, "Backup inválido ou vazio: {$latest}\n");
    exit(1);
}

echo json_encode([
    'ok' => true,
    'file' => basename($latest),
    'size' => $size,
    'note' => 'Restore manual: mysql ... < arquivo.sql',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
