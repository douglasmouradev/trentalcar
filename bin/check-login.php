<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

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

$pdo = Database::pdo();
echo "DB OK\n";

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
