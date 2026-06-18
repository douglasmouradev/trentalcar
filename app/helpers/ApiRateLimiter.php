<?php

declare(strict_types=1);

/**
 * Limita chamadas à API autenticada por IP.
 */
final class ApiRateLimiter
{
    private const MAX = 240;
    private const WINDOW = 300;

    private static function path(): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $dir . '/api_' . $ip . '.json';
    }

    public static function hit(): bool
    {
        $now = time();
        $snap = FileRateStore::mutate(self::path(), static function (?array $data) use ($now): array {
            if (!is_array($data) || !isset($data['count'], $data['first_at']) || $now - (int) $data['first_at'] > self::WINDOW) {
                return ['count' => 1, 'first_at' => $now];
            }
            return [
                'count' => (int) $data['count'] + 1,
                'first_at' => (int) $data['first_at'],
            ];
        });
        return (int) ($snap['count'] ?? 0) <= self::MAX;
    }

    public static function guardJson(): void
    {
        if (self::hit()) {
            return;
        }
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'rate_limited'], JSON_THROW_ON_ERROR);
        exit;
    }
}
