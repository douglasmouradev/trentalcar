<?php

declare(strict_types=1);

/** Rate limit para tentativas de código TOTP (login e activação 2FA). */
final class TotpRateLimiter
{
    private const MAX = 8;
    private const WINDOW = 900;

    private static function path(string $scope): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $dir . '/totp_' . $scope . '_' . $ip . '.json';
    }

    public static function tooManyAttempts(string $scope = 'login'): bool
    {
        $data = FileRateStore::read(self::path($scope));
        if (!is_array($data) || !isset($data['attempts'], $data['first_at'])) {
            return false;
        }
        $now = time();
        if ($now - (int) $data['first_at'] > self::WINDOW) {
            return false;
        }
        return (int) $data['attempts'] >= self::MAX;
    }

    public static function hit(string $scope = 'login'): void
    {
        $now = time();
        FileRateStore::mutate(self::path($scope), static function (?array $data) use ($now): array {
            if (!is_array($data) || !isset($data['attempts'], $data['first_at']) || $now - (int) $data['first_at'] > self::WINDOW) {
                return ['attempts' => 1, 'first_at' => $now];
            }
            return [
                'attempts' => (int) $data['attempts'] + 1,
                'first_at' => (int) $data['first_at'],
            ];
        });
    }

    public static function clear(string $scope = 'login'): void
    {
        $p = self::path($scope);
        if (is_file($p)) {
            unlink($p);
        }
    }
}
