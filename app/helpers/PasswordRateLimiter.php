<?php

declare(strict_types=1);

/** Rate limit para tentativas de senha (troca, 2FA disable/begin). */
final class PasswordRateLimiter
{
    private const MAX = 8;
    private const WINDOW = 900;

    private static function path(int $userId, string $scope): string
    {
        $dir = BASE_PATH . '/storage/logs';
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $dir . '/pwd_' . $scope . '_' . $userId . '_' . $ip . '.json';
    }

    public static function tooManyAttempts(int $userId, string $scope = 'account'): bool
    {
        $data = FileRateStore::read(self::path($userId, $scope));
        if (!is_array($data) || !isset($data['attempts'], $data['first_at'])) {
            return false;
        }
        $now = time();
        if ($now - (int) $data['first_at'] > self::WINDOW) {
            return false;
        }
        return (int) $data['attempts'] >= self::MAX;
    }

    public static function hit(int $userId, string $scope = 'account'): void
    {
        $now = time();
        FileRateStore::mutate(self::path($userId, $scope), static function (?array $data) use ($now): array {
            if (!is_array($data) || !isset($data['attempts'], $data['first_at']) || $now - (int) $data['first_at'] > self::WINDOW) {
                return ['attempts' => 1, 'first_at' => $now];
            }
            return [
                'attempts' => (int) $data['attempts'] + 1,
                'first_at' => (int) $data['first_at'],
            ];
        });
    }

    public static function clear(int $userId, string $scope = 'account'): void
    {
        $p = self::path($userId, $scope);
        if (is_file($p)) {
            unlink($p);
        }
    }
}
