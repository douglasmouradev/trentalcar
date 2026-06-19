<?php

declare(strict_types=1);

final class HealthController
{
    public function index(): void
    {
        $app = Config::app();
        $token = (string) ($app['health_token'] ?? '');
        $isProd = ($app['env'] ?? 'production') === 'production';

        if ($isProd && $token === '') {
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'misconfigured'], JSON_THROW_ON_ERROR);
            return;
        }

        if ($token !== '') {
            $given = (string) ($_GET['token'] ?? $_SERVER['HTTP_X_HEALTH_TOKEN'] ?? '');
            if (!hash_equals($token, $given)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'forbidden'], JSON_THROW_ON_ERROR);
                return;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        $checks = ['app' => true, 'database' => false, 'storage' => false];
        $metrics = [];

        $dbStart = microtime(true);
        try {
            Database::pdo()->query('SELECT 1');
            $checks['database'] = true;
            $metrics['db_ms'] = round((microtime(true) - $dbStart) * 1000, 2);
            if (Schema::hasTable('mail_outbox')) {
                $metrics['mail_pending'] = MailOutbox::countPending();
            }
            if (Schema::hasTable('leads')) {
                $metrics['leads_new'] = Lead::countNew();
            }
        } catch (Throwable) {
            $checks['database'] = false;
        }

        $writable = is_writable(BASE_PATH . '/storage/logs')
            && is_writable(BASE_PATH . '/public/assets/uploads');
        $customersDir = BASE_PATH . '/storage/customers';
        if (is_dir($customersDir)) {
            $writable = $writable && is_writable($customersDir);
        }
        $checks['storage'] = $writable;

        $freeMb = @disk_free_space(BASE_PATH);
        if ($freeMb !== false) {
            $metrics['disk_free_mb'] = round($freeMb / 1048576, 1);
        }
        $metrics['php'] = PHP_VERSION;

        if (Schema::hasTable('mail_outbox')) {
            try {
                $metrics['mail_failed'] = (int) Database::pdo()
                    ->query("SELECT COUNT(*) FROM mail_outbox WHERE status = 'failed'")
                    ->fetchColumn();
            } catch (Throwable) {
                $metrics['mail_failed'] = -1;
            }
        }

        $ok = $checks['database'] && $checks['storage'];
        if (isset($metrics['disk_free_mb']) && $metrics['disk_free_mb'] < 100) {
            $ok = false;
            $checks['disk_space'] = false;
        } else {
            $checks['disk_space'] = true;
        }
        http_response_code($ok ? 200 : 503);
        echo json_encode(['ok' => $ok, 'checks' => $checks, 'metrics' => $metrics, 'time' => date('c')], JSON_THROW_ON_ERROR);
    }
}
