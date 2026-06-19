<?php

declare(strict_types=1);

/** Limita envios do formulário público de lead (landing). */
final class LeadRateLimiter
{
    private const MAX = 12;
    private const WINDOW = 3600;

    public static function tooMany(): bool
    {
        return DbRateLimiter::tooMany(DbRateLimiter::clientBucket('lead'), self::MAX, self::WINDOW);
    }

    public static function hit(): void
    {
        DbRateLimiter::hit(DbRateLimiter::clientBucket('lead'), self::WINDOW);
    }
}
