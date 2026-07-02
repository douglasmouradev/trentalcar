<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

$pdo = Database::pdo();
echo "DB OK\n";

if (in_array('--clear-rate-limits', $argv ?? [], true)) {
    ProductionGuard::abortIfProduction('limpar rate limits');
    $rows = $pdo->query("SELECT bucket_key, hits, window_start FROM rate_limits WHERE bucket_key LIKE 'login%' ORDER BY bucket_key")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows === []) {
        echo "rate limits (login): nenhum\n";
    } else {
        echo "rate limits (login) antes:\n";
        foreach ($rows as $row) {
            $age = time() - (int) $row['window_start'];
            echo "  {$row['bucket_key']} hits={$row['hits']} age={$age}s\n";
        }
    }
    $n = $pdo->exec("DELETE FROM rate_limits WHERE bucket_key LIKE 'login%'");
    echo "cleared " . (int) $n . " login rate limit row(s)\n";
}

$demoHash = '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq';
if (in_array('--fix-demo', $argv ?? [], true)) {
    $fix = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
    foreach (['owner@titaniumrental.com', 'operator@titaniumrental.com', 'partner@titaniumrental.com'] as $email) {
        $fix->execute([$demoHash, $email]);
        echo "fixed {$email}\n";
    }
}

$emails = ['partner@titaniumrental.com', 'owner@titaniumrental.com', 'operator@titaniumrental.com'];
foreach ($emails as $email) {
    $stmt = $pdo->prepare('SELECT id, email, role, is_active, password_hash, must_change_password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        echo "{$email}: NOT FOUND\n";
        continue;
    }
    $ok = password_verify('password123', (string) $u['password_hash']);
    echo "{$email}: id={$u['id']} role={$u['role']} active={$u['is_active']} must_change={$u['must_change_password']} pass_ok=" . ($ok ? 'yes' : 'no') . "\n";
    echo "  hash_prefix=" . substr((string) $u['password_hash'], 0, 20) . "...\n";
}

$m = $pdo->query('SELECT filename FROM schema_migrations ORDER BY filename')->fetchAll(PDO::FETCH_COLUMN);
echo "migrations: " . implode(', ', $m) . "\n";

echo "\nAll users:\n";
foreach ($pdo->query('SELECT id, email, role, is_active FROM users ORDER BY id') as $row) {
    echo "  #{$row['id']} {$row['email']} {$row['role']} active={$row['is_active']}\n";
}
