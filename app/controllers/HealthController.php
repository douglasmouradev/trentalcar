<?php

declare(strict_types=1);

final class HealthController
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $ok = true;
        $checks = [
            'app' => true,
            'database' => false,
        ];
        $details = [
            'env' => $_ENV['APP_ENV'] ?? 'unknown',
            'time' => gmdate('c'),
        ];
        try {
            $started = microtime(true);
            Database::pdo()->query('SELECT 1');
            $checks['database'] = true;
            $details['db_ms'] = (int) round((microtime(true) - $started) * 1000);
        } catch (Throwable) {
            $ok = false;
        }
        try {
            Database::pdo()->query('SELECT 1 FROM schema_migrations LIMIT 1');
            $details['migrations'] = true;
        } catch (Throwable) {
            $details['migrations'] = false;
        }
        http_response_code($ok ? 200 : 503);
        echo json_encode(['ok' => $ok, 'checks' => $checks, 'details' => $details], JSON_THROW_ON_ERROR);
    }
}
