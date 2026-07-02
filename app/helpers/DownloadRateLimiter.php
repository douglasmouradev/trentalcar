<?php

declare(strict_types=1);

/** Rate limit para downloads autenticados sensíveis (anexos, exports). */
final class DownloadRateLimiter
{
    private const MAX = 40;
    private const WINDOW = 60;

    public static function tooMany(?int $userId): bool
    {
        if ($userId === null) {
            return true;
        }

        return DbRateLimiter::tooMany('download:user:' . $userId, self::MAX, self::WINDOW);
    }

    public static function hit(?int $userId): void
    {
        if ($userId === null) {
            return;
        }
        DbRateLimiter::hit('download:user:' . $userId, self::WINDOW);
    }
}
