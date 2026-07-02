<?php

declare(strict_types=1);

/** Tarefas de limpeza periódica na base de dados. */
final class DbMaintenance
{
    /** Remove janelas de rate limit expiradas (padrão: >24h). */
    public static function purgeExpiredRateLimits(int $maxAgeSeconds = 86400): int
    {
        $cutoff = time() - $maxAgeSeconds;
        $stmt = Database::prepare('DELETE FROM rate_limits WHERE window_start < ?');
        $stmt->execute([$cutoff]);

        return $stmt->rowCount();
    }

    /** Remove tokens de reset expirados ou já utilizados há mais de 7 dias. */
    public static function purgePasswordResetTokens(): int
    {
        $stmt = Database::prepare(
            'DELETE FROM password_reset_tokens
             WHERE expires_at < NOW()
                OR (used_at IS NOT NULL AND used_at < DATE_SUB(NOW(), INTERVAL 7 DAY))'
        );
        $stmt->execute();

        return $stmt->rowCount();
    }
}
