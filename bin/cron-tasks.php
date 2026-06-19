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

passthru('php ' . escapeshellarg(BASE_PATH . '/bin/rotate-logs.php'), $r1);
passthru('php ' . escapeshellarg(BASE_PATH . '/bin/process-mail.php'), $r2);

$hour = (int) date('G');
if ($hour === 3) {
    passthru('php ' . escapeshellarg(BASE_PATH . '/bin/backup.php'), $r3);
}

echo json_encode([
    'rotate' => $r1,
    'mail' => $r2,
    'backup_skipped' => $hour !== 3,
], JSON_PRETTY_PRINT) . PHP_EOL;
