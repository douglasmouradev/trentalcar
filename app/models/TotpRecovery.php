<?php

declare(strict_types=1);

final class TotpRecovery
{
    private const CODE_COUNT = 8;

    /** @return list<string> Códigos em texto claro (mostrar uma vez ao utilizador) */
    public static function regenerate(int $userId): array
    {
        if (!Schema::hasTable('totp_recovery_codes')) {
            return [];
        }
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM totp_recovery_codes WHERE user_id = ?')->execute([$userId]);

        $plain = [];
        $stmt = $pdo->prepare('INSERT INTO totp_recovery_codes (user_id, code_hash) VALUES (?, ?)');
        for ($i = 0; $i < self::CODE_COUNT; $i++) {
            $code = self::generatePlainCode();
            $plain[] = $code;
            $stmt->execute([$userId, hash('sha256', self::normalize($code))]);
        }
        return $plain;
    }

    public static function verifyAndConsume(int $userId, string $code): bool
    {
        if (!Schema::hasTable('totp_recovery_codes')) {
            return false;
        }
        $norm = self::normalize($code);
        if ($norm === '') {
            return false;
        }
        $hash = hash('sha256', $norm);
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'SELECT id FROM totp_recovery_codes WHERE user_id = ? AND code_hash = ? AND used_at IS NULL LIMIT 1 FOR UPDATE'
        );
        $stmt->execute([$userId, $hash]);
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->rollBack();
            return false;
        }
        $pdo->prepare('UPDATE totp_recovery_codes SET used_at = NOW() WHERE id = ?')->execute([(int) $row['id']]);
        $pdo->commit();
        return true;
    }

    public static function clear(int $userId): void
    {
        if (!Schema::hasTable('totp_recovery_codes')) {
            return;
        }
        Database::pdo()->prepare('DELETE FROM totp_recovery_codes WHERE user_id = ?')->execute([$userId]);
    }

    public static function remainingCount(int $userId): int
    {
        if (!Schema::hasTable('totp_recovery_codes')) {
            return 0;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM totp_recovery_codes WHERE user_id = ? AND used_at IS NULL'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    private static function generatePlainCode(): string
    {
        $a = strtoupper(bin2hex(random_bytes(2)));
        $b = strtoupper(bin2hex(random_bytes(2)));
        return $a . '-' . $b;
    }

    private static function normalize(string $code): string
    {
        $code = strtoupper(preg_replace('/\s+/', '', trim($code)) ?? '');
        if (preg_match('/^[A-F0-9]{4}-?[A-F0-9]{4}$/', $code) !== 1) {
            return '';
        }
        if (!str_contains($code, '-')) {
            $code = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        }
        return $code;
    }
}
