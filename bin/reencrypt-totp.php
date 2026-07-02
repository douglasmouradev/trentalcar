#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

if (trim((string) ($_ENV['APP_KEY'] ?? '')) === '') {
    fwrite(STDERR, "APP_KEY is required.\n");
    exit(1);
}

if (!Schema::hasColumn('users', 'totp_secret')) {
    echo "totp_secret column missing — skip\n";
    exit(0);
}

$pdo = Database::pdo();
$rows = $pdo->query('SELECT id, totp_secret FROM users WHERE totp_secret IS NOT NULL AND totp_secret != \'\'')->fetchAll();
$updated = 0;
$stmt = $pdo->prepare('UPDATE users SET totp_secret = ? WHERE id = ?');

foreach ($rows as $row) {
    $stored = (string) $row['totp_secret'];
    if (SecretCipher::isEncrypted($stored)) {
        continue;
    }
    $stmt->execute([SecretCipher::encrypt($stored), (int) $row['id']]);
    $updated++;
    echo "encrypted user #{$row['id']}\n";
}

echo "done — {$updated} secret(s) re-encrypted\n";
