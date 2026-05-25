<?php

declare(strict_types=1);

final class PasswordReset
{
    private const TTL_SECONDS = 3600;

    public static function createForUser(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expires = gmdate('Y-m-d H:i:s', time() + self::TTL_SECONDS);
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?')->execute([$userId]);
        $pdo->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        )->execute([$userId, $hash, $expires]);
        return $token;
    }

    public static function findValidUserId(string $token): ?int
    {
        $hash = hash('sha256', $token);
        $stmt = Database::pdo()->prepare(
            'SELECT user_id FROM password_reset_tokens WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1'
        );
        $stmt->execute([$hash]);
        $uid = $stmt->fetchColumn();
        return $uid !== false ? (int) $uid : null;
    }

    public static function consume(string $token): ?int
    {
        $userId = self::findValidUserId($token);
        if ($userId === null) {
            return null;
        }
        $hash = hash('sha256', $token);
        Database::pdo()->prepare('DELETE FROM password_reset_tokens WHERE token_hash = ?')->execute([$hash]);
        return $userId;
    }
}
