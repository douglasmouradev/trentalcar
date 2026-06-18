<?php

declare(strict_types=1);

final class LoginRateLimiter
{
    private const MAX = 5;
    private const WINDOW = 900;

    private static function ipPath(): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $dir . '/login_' . $ip . '.json';
    }

    private static function emailPath(string $email): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $hash = hash('sha256', strtolower(trim($email)));
        return $dir . '/login_email_' . $hash . '.json';
    }

    public static function tooManyAttempts(?string $email = null): bool
    {
        if (self::isOverLimit(self::ipPath())) {
            return true;
        }
        if ($email !== null && $email !== '') {
            return self::isOverLimit(self::emailPath($email));
        }
        return false;
    }

    public static function hit(?string $email = null): void
    {
        self::recordHit(self::ipPath());
        if ($email !== null && $email !== '') {
            self::recordHit(self::emailPath($email));
        }
    }

    public static function clear(?string $email = null): void
    {
        self::remove(self::ipPath());
        if ($email !== null && $email !== '') {
            self::remove(self::emailPath($email));
        }
    }

    private static function isOverLimit(string $path): bool
    {
        $data = FileRateStore::read($path);
        if (!is_array($data) || !isset($data['attempts'], $data['first_at'])) {
            return false;
        }
        $now = time();
        if ($now - (int) $data['first_at'] > self::WINDOW) {
            return false;
        }
        return (int) $data['attempts'] >= self::MAX;
    }

    private static function recordHit(string $path): void
    {
        $now = time();
        FileRateStore::mutate($path, static function (?array $data) use ($now): array {
            if (!is_array($data) || !isset($data['attempts'], $data['first_at']) || $now - (int) $data['first_at'] > self::WINDOW) {
                return ['attempts' => 1, 'first_at' => $now];
            }
            return [
                'attempts' => (int) $data['attempts'] + 1,
                'first_at' => (int) $data['first_at'],
            ];
        });
    }

    private static function remove(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
