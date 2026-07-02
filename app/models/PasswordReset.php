<?php

declare(strict_types=1);

final class PasswordReset
{
    public static function create(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $pdo = Database::pdo();
        Database::prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')
            ->execute([$userId]);
        $stmt = Database::prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $stmt->execute([$userId, $hash]);
        return $token;
    }

    public static function findValidUserId(string $token): ?int
    {
        $hash = hash('sha256', $token);
        $stmt = Database::prepare(
            'SELECT user_id FROM password_reset_tokens
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$hash]);
        $uid = $stmt->fetchColumn();
        return $uid !== false ? (int) $uid : null;
    }

    public static function consume(string $token): void
    {
        $hash = hash('sha256', $token);
        Database::prepare(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL'
        )->execute([$hash]);
    }
}
