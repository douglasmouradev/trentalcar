#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

Env::hydrateFromGetenv([
    'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_URL',
]);

try {
    Database::pdo()->query('SELECT 1');
} catch (Throwable $e) {
    fwrite(STDERR, 'DB connection failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$user = User::findByEmail('owner@titaniumrental.com');
if ($user === null) {
    fwrite(STDERR, "owner user missing from seed\n");
    exit(1);
}
if (!password_verify('password123', (string) $user['password_hash'])) {
    fwrite(
        STDERR,
        'password123 invalid for owner (hash: ' . substr((string) $user['password_hash'], 0, 29) . "…)\n"
    );
    exit(1);
}

$smokeBase = getenv('SMOKE_BASE_URL');
if ($smokeBase !== false && trim($smokeBase) !== '') {
    $base = rtrim(trim($smokeBase), '/');
    foreach (['/login', '/'] as $path) {
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
        $body = @file_get_contents($base . $path, false, $ctx);
        $code = 0;
        if (function_exists('http_get_last_response_headers')) {
            $headers = http_get_last_response_headers();
            if ($headers !== false && isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
                $code = (int) $m[1];
            }
        }
        if ($code !== 200) {
            fwrite(STDERR, "HTTP {$path} returned {$code}\n");
            exit(1);
        }
        if (!is_string($body) || trim($body) === '') {
            fwrite(STDERR, "HTTP {$path} returned empty body\n");
            exit(1);
        }
    }
}

fwrite(STDOUT, "ci-preflight ok\n");
