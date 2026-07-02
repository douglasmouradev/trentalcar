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
    if (!extension_loaded('curl')) {
        fwrite(STDERR, "ext-curl required for HTTP preflight\n");
        exit(1);
    }
    $base = rtrim(trim($smokeBase), '/');
    foreach (['/login', '/'] as $path) {
        $url = $base . $path;
        $ch = curl_init($url);
        if ($ch === false) {
            fwrite(STDERR, "curl_init failed for {$path}\n");
            exit(1);
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
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
