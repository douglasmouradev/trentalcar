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

/** @return list<string> */
function migrate_split_sql(string $sql): array
{
    $sql = preg_replace('/<\?php.*?(?:\?>|$)/s', '', $sql) ?? $sql;
    $sql = preg_replace('/^\s*declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;\s*/m', '', $sql) ?? $sql;
    $lines = preg_split('/\R/', $sql) ?: [];
    $buffer = '';
    $statements = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }
        if (preg_match('/^USE\s+/i', $trim)) {
            continue;
        }
        $buffer .= $line . "\n";
        if (str_ends_with(rtrim($line), ';')) {
            $stmt = trim($buffer);
            $buffer = '';
            if ($stmt !== '' && $stmt !== ';') {
                $statements[] = $stmt;
            }
        }
    }
    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }
    return $statements;
}

function migrate_is_benign(PDOException $e): bool
{
    $code = (int) ($e->errorInfo[1] ?? 0);
    return in_array($code, [
        1050, // table exists
        1060, // duplicate column
        1061, // duplicate key
        1091, // drop missing column/key
        1062, // duplicate entry (updates idempotentes)
    ], true);
}

$pdo = Database::pdo();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        filename VARCHAR(255) NOT NULL PRIMARY KEY,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$dir = BASE_PATH . '/database/migrations';
$files = glob($dir . '/*.sql') ?: [];
sort($files);

$applied = $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$applied = is_array($applied) ? array_flip($applied) : [];

foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        echo "skip {$name}\n";
        continue;
    }
    $raw = trim((string) file_get_contents($file));
    if ($raw === '' || preg_match('/^\s*--/m', $raw) && !preg_match('/^\s*(ALTER|CREATE|INSERT|UPDATE|DELETE)/im', $raw)) {
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (?)');
        $stmt->execute([$name]);
        echo "skip {$name} (vazio/documentação)\n";
        continue;
    }

    $statements = migrate_split_sql($raw);
    if ($statements === []) {
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (?)');
        $stmt->execute([$name]);
        echo "skip {$name} (sem SQL executável)\n";
        continue;
    }

    echo "apply {$name}… ";
    $hadBenign = false;
    try {
        foreach ($statements as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                if (migrate_is_benign($e)) {
                    $hadBenign = true;
                    continue;
                }
                throw $e;
            }
        }
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (?)');
        $stmt->execute([$name]);
        echo $hadBenign ? "ok (já existia)\n" : "ok\n";
    } catch (Throwable $e) {
        echo "fail\n";
        fwrite(STDERR, $e->getMessage() . "\n");
        exit(1);
    }
}

echo "done\n";
