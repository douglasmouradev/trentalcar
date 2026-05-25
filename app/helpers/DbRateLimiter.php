<?php

declare(strict_types=1);

/**
 * Rate limiting em base de dados — partilhado entre instâncias.
 */
final class DbRateLimiter
{
    public static function tooMany(string $bucket, int $max, int $windowSeconds): bool
    {
        try {
            $stmt = Database::pdo()->prepare('SELECT hits, window_start FROM rate_limits WHERE bucket_key = ?');
            $stmt->execute([$bucket]);
            $row = $stmt->fetch();
            if (!$row) {
                return false;
            }
            if (time() - (int) $row['window_start'] > $windowSeconds) {
                return false;
            }
            return (int) $row['hits'] >= $max;
        } catch (Throwable $e) {
            AppError::log($e);
            if (ProductionGuard::isProduction()) {
                return true;
            }
            return FileRateLimiter::tooMany($bucket, $max, $windowSeconds);
        }
    }

    public static function hit(string $bucket, int $windowSeconds): int
    {
        try {
            $pdo = Database::pdo();
            $now = time();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT hits, window_start FROM rate_limits WHERE bucket_key = ? FOR UPDATE');
            $stmt->execute([$bucket]);
            $row = $stmt->fetch();
            if (!$row || $now - (int) $row['window_start'] > $windowSeconds) {
                $upsert = $pdo->prepare(
                    'INSERT INTO rate_limits (bucket_key, hits, window_start) VALUES (?, 1, ?)
                     ON DUPLICATE KEY UPDATE hits = 1, window_start = VALUES(window_start)'
                );
                $upsert->execute([$bucket, $now]);
                $pdo->commit();
                return 1;
            }
            $hits = (int) $row['hits'] + 1;
            $upd = $pdo->prepare('UPDATE rate_limits SET hits = ? WHERE bucket_key = ?');
            $upd->execute([$hits, $bucket]);
            $pdo->commit();
            return $hits;
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            AppError::log($e);
            if (ProductionGuard::isProduction()) {
                return 1;
            }
            return FileRateLimiter::hit($bucket, $windowSeconds);
        }
    }

    public static function clear(string $bucket): void
    {
        try {
            $stmt = Database::pdo()->prepare('DELETE FROM rate_limits WHERE bucket_key = ?');
            $stmt->execute([$bucket]);
        } catch (Throwable) {
            FileRateLimiter::clear($bucket);
        }
    }

    public static function clientBucket(string $prefix): string
    {
        $ip = preg_replace('/[^a-fA-F0-9.:]/', '_', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return $prefix . ':' . $ip;
    }
}
