<?php

declare(strict_types=1);

final class AppLog
{
    public static function info(string $event, array $context = []): void
    {
        self::write('info', $event, $context);
    }

    public static function error(string $event, array $context = []): void
    {
        self::write('error', $event, $context);
    }

    /** @param array<string, mixed> $context */
    private static function write(string $level, string $event, array $context): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? ($_SESSION['request_id'] ?? bin2hex(random_bytes(8)));
        $line = json_encode([
            'time' => gmdate('c'),
            'level' => $level,
            'event' => $event,
            'request_id' => $requestId,
            'context' => $context,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        file_put_contents($dir . '/app.jsonl', $line . "\n", FILE_APPEND | LOCK_EX);
    }
}
