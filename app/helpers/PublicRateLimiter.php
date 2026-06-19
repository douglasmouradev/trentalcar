<?php

declare(strict_types=1);

/** Rate limit para endpoints públicos sensíveis (consulta, recuperação de senha). */
final class PublicRateLimiter
{
    private const FORGOT_MAX = 5;
    private const CONSULT_MAX = 12;
    private const WINDOW = 900;

    public static function forgotBlocked(): bool
    {
        return DbRateLimiter::tooMany(self::forgotBucket(), self::FORGOT_MAX, self::WINDOW);
    }

    public static function hitForgot(): void
    {
        DbRateLimiter::hit(self::forgotBucket(), self::WINDOW);
    }

    public static function consultBlocked(): bool
    {
        return DbRateLimiter::tooMany(self::consultBucket(), self::CONSULT_MAX, self::WINDOW);
    }

    public static function hitConsult(): void
    {
        DbRateLimiter::hit(self::consultBucket(), self::WINDOW);
    }

    private static function forgotBucket(): string
    {
        return DbRateLimiter::clientBucket('forgot');
    }

    private static function consultBucket(): string
    {
        return DbRateLimiter::clientBucket('consult');
    }
}
