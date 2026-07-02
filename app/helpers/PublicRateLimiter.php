<?php

declare(strict_types=1);

/** Rate limit para endpoints públicos sensíveis (consulta, recuperação de senha). */
final class PublicRateLimiter
{
    private const FORGOT_MAX = 5;
    private const CONSULT_MAX = 12;
    private const WINDOW = 900;

    public static function forgotBlocked(?string $email = null): bool
    {
        if (DbRateLimiter::tooMany(self::forgotBucket(), self::FORGOT_MAX, self::WINDOW)) {
            return true;
        }
        if ($email !== null && $email !== '') {
            $hash = hash('sha256', strtolower(trim($email)));

            return DbRateLimiter::tooMany('forgot:email:' . $hash, self::FORGOT_MAX, self::WINDOW);
        }

        return false;
    }

    public static function hitForgot(?string $email = null): void
    {
        DbRateLimiter::hit(self::forgotBucket(), self::WINDOW);
        if ($email !== null && $email !== '') {
            $hash = hash('sha256', strtolower(trim($email)));
            DbRateLimiter::hit('forgot:email:' . $hash, self::WINDOW);
        }
    }

    private const RESET_MAX = 10;

    public static function resetBlocked(): bool
    {
        return DbRateLimiter::tooMany(self::resetBucket(), self::RESET_MAX, self::WINDOW);
    }

    public static function hitReset(): void
    {
        DbRateLimiter::hit(self::resetBucket(), self::WINDOW);
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

    private static function resetBucket(): string
    {
        return DbRateLimiter::clientBucket('reset');
    }
}
