<?php

declare(strict_types=1);

final class MailOutbox
{
    public static function enqueue(string $to, string $subject, string $body): int
    {
        $stmt = Database::prepare(
            'INSERT INTO mail_outbox (to_email, subject, body, status) VALUES (?,?,?,?)'
        );
        $stmt->execute([trim($to), $subject, $body, 'pending']);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public static function pending(int $limit = 20): array
    {
        $stmt = Database::prepare(
            "SELECT * FROM mail_outbox WHERE status = 'pending' AND attempts < 5 ORDER BY id ASC LIMIT ?"
        );
        $stmt->bindValue(1, max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function markSent(int $id): void
    {
        Database::prepare(
            "UPDATE mail_outbox SET status = 'sent', sent_at = NOW() WHERE id = ?"
        )->execute([$id]);
    }

    public static function markFailed(int $id, string $error): void
    {
        Database::prepare(
            "UPDATE mail_outbox SET
                attempts = attempts + 1,
                last_error = ?,
                status = IF(attempts + 1 >= 5, 'failed', 'pending')
             WHERE id = ?"
        )->execute([mb_substr($error, 0, 500), $id]);
    }

    public static function retryPending(int $id): void
    {
        Database::prepare(
            "UPDATE mail_outbox SET status = 'pending', attempts = attempts + 1 WHERE id = ?"
        )->execute([$id]);
    }

    public static function countPending(): int
    {
        return (int) Database::query("SELECT COUNT(*) FROM mail_outbox WHERE status = 'pending'")->fetchColumn();
    }
}
