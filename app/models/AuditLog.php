<?php

declare(strict_types=1);

final class AuditLog
{
    /** @return array<int, array<string, mixed>> */
    public static function recent(int $limit = 100): array
    {
        $stmt = Database::prepare(
            'SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginated(int $page, int $perPage): array
    {
        $total = (int) Database::query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $stmt = Database::prepare(
            'SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC LIMIT ' . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset']
        );
        $stmt->execute();
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }
}
