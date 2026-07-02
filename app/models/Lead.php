<?php

declare(strict_types=1);

final class Lead
{
    public const STATUSES = ['new', 'contacted', 'converted', 'discarded'];

    /** @param array<string, mixed> $d */
    public static function create(array $d): int
    {
        $stmt = Database::prepare(
            'INSERT INTO leads (full_name, email, phone, local, inicio, fim, mesmo_local, local_devolucao, car_id, ip_hash)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['full_name'],
            $d['email'],
            $d['phone'],
            $d['local'],
            $d['inicio'],
            $d['fim'],
            (int) ($d['mesmo_local'] ?? 1),
            $d['local_devolucao'] ?? null,
            $d['car_id'] ?? null,
            $d['ip_hash'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $sql = 'SELECT l.*, c.brand AS car_brand, c.model AS car_model, c.license_plate AS car_plate
                FROM leads l
                LEFT JOIN cars c ON c.id = l.car_id
                WHERE l.id = ?';
        $stmt = Database::prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginated(int $page, int $perPage, ?string $status = null, ?string $q = null): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        if ($status !== null && $status !== '' && in_array($status, self::STATUSES, true)) {
            $where .= ' AND l.status = ?';
            $params[] = $status;
        }
        if ($q !== null && trim($q) !== '') {
            $like = '%' . trim($q) . '%';
            $where .= ' AND (l.full_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.local LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        $base = ' FROM leads l LEFT JOIN cars c ON c.id = l.car_id';
        $stmt = Database::prepare('SELECT COUNT(*)' . $base . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT l.*, c.brand AS car_brand, c.model AS car_model, c.license_plate AS car_plate'
            . $base . $where . ' ORDER BY l.created_at DESC LIMIT '
            . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    public static function updateStatus(int $id, string $status, ?string $notes = null): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            return;
        }
        $stmt = Database::prepare('UPDATE leads SET status = ?, notes = COALESCE(?, notes) WHERE id = ?');
        $stmt->execute([$status, $notes, $id]);
    }

    public static function countNew(): int
    {
        return (int) Database::query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
    }

    public static function countStale(int $hours = 24): int
    {
        $hours = max(1, $hours);
        $stmt = Database::prepare(
            "SELECT COUNT(*) FROM leads WHERE status = 'new' AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)"
        );
        $stmt->execute([$hours]);
        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, array<string, mixed>> */
    public static function recentNew(int $limit = 5): array
    {
        $stmt = Database::prepare(
            "SELECT id, full_name, email, inicio, fim, created_at FROM leads WHERE status = 'new' ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->bindValue(1, max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
