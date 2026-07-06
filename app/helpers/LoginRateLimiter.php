<?php

declare(strict_types=1);

final class LoginRateLimiter
{
    private const MAX = 5;
    private const WINDOW = 900;

    public static function tooManyAttempts(?string $email = null): bool
    {
        if (ProductionGuard::isDeployed()
            && DbRateLimiter::tooMany(DbRateLimiter::clientBucket('login'), self::MAX, self::WINDOW)) {
            return true;
        }
        if ($email !== null && $email !== '') {
            $hash = hash('sha256', strtolower(trim($email)));
            return DbRateLimiter::tooMany('login:email:' . $hash, self::MAX, self::WINDOW);
        }
        return false;
    }

    public static function hit(?string $email = null): void
    {
        if (ProductionGuard::isDeployed()) {
            DbRateLimiter::hit(DbRateLimiter::clientBucket('login'), self::WINDOW);
        }
        if ($email !== null && $email !== '') {
            $hash = hash('sha256', strtolower(trim($email)));
            DbRateLimiter::hit('login:email:' . $hash, self::WINDOW);
        }
    }

    public static function clear(?string $email = null): void
    {
        if (ProductionGuard::isDeployed()) {
            DbRateLimiter::clear(DbRateLimiter::clientBucket('login'));
        }
        if ($email !== null && $email !== '') {
            $hash = hash('sha256', strtolower(trim($email)));
            DbRateLimiter::clear('login:email:' . $hash);
        }
    }
}
