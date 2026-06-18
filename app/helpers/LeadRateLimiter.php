<?php

declare(strict_types=1);

/** Limita envios do formulário público de lead (landing). */
final class LeadRateLimiter
{
    private const MAX = 12;
    private const WINDOW = 3600;

    private static function path(): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $dir . '/lead_' . $ip . '.json';
    }

    public static function tooMany(): bool
    {
        $data = FileRateStore::read(self::path());
        if (!is_array($data) || !isset($data['count'], $data['first_at'])) {
            return false;
        }
        $now = time();
        if ($now - (int) $data['first_at'] > self::WINDOW) {
            return false;
        }
        return (int) $data['count'] >= self::MAX;
    }

    public static function hit(): void
    {
        $now = time();
        FileRateStore::mutate(self::path(), static function (?array $data) use ($now): array {
            if (!is_array($data) || !isset($data['count'], $data['first_at']) || $now - (int) $data['first_at'] > self::WINDOW) {
                return ['count' => 1, 'first_at' => $now];
            }
            return [
                'count' => (int) $data['count'] + 1,
                'first_at' => (int) $data['first_at'],
            ];
        });
    }
}
