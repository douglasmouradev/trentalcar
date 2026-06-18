<?php

declare(strict_types=1);

/** Sessão pendente de 2FA com binding a IP + User-Agent. */
final class PendingTwoFactor
{
    private const TTL = 300;

    public static function begin(int $userId): void
    {
        $_SESSION['pending_2fa_user_id'] = $userId;
        $_SESSION['pending_2fa_until'] = time() + self::TTL;
        $_SESSION['pending_2fa_bind'] = self::fingerprint();
    }

    public static function isActive(): bool
    {
        $uid = (int) ($_SESSION['pending_2fa_user_id'] ?? 0);
        $until = (int) ($_SESSION['pending_2fa_until'] ?? 0);
        if ($uid <= 0 || $until < time()) {
            return false;
        }
        $bind = (string) ($_SESSION['pending_2fa_bind'] ?? '');
        return $bind !== '' && hash_equals($bind, self::fingerprint());
    }

    public static function userId(): int
    {
        return (int) ($_SESSION['pending_2fa_user_id'] ?? 0);
    }

    public static function clear(): void
    {
        unset(
            $_SESSION['pending_2fa_user_id'],
            $_SESSION['pending_2fa_until'],
            $_SESSION['pending_2fa_bind'],
        );
    }

    private static function fingerprint(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return hash('sha256', $ip . '|' . $ua);
    }
}
