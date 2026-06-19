<?php

declare(strict_types=1);

/**
 * Limita chamadas à API autenticada por IP.
 */
final class ApiRateLimiter
{
    private const MAX = 240;
    private const WINDOW = 300;

    public static function hit(): bool
    {
        $hits = DbRateLimiter::hit(DbRateLimiter::clientBucket('api'), self::WINDOW);
        return $hits <= self::MAX;
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
