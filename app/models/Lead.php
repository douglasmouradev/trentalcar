<?php

declare(strict_types=1);

final class Lead
{
    /** @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int} */
    public static function paginated(int $page, int $perPage, ?string $status = null): array
    {
        $where = '';
        $params = [];
        if ($status !== null && $status !== '') {
            $where = ' WHERE status = ?';
            $params[] = $status;
        }
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM leads' . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT * FROM leads' . $where . ' ORDER BY created_at DESC LIMIT '
            . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM leads WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO leads (location_text, start_date, end_date, same_location, return_location_text,
             contact_name, contact_email, contact_phone, ip_hash, status)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['location_text'],
            $d['start_date'],
            $d['end_date'],
            (int) ($d['same_location'] ?? 1),
            $d['return_location_text'] ?? null,
            $d['contact_name'] ?? null,
            $d['contact_email'] ?? null,
            $d['contact_phone'] ?? null,
            $d['ip_hash'],
            $d['status'] ?? 'new',
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function exportRows(?string $status = null): array
    {
        $where = '';
        $params = [];
        if ($status !== null && $status !== '') {
            $where = ' WHERE status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT id, location_text, start_date, end_date, contact_name, contact_phone, contact_email, status, created_at'
            . ' FROM leads' . $where . ' ORDER BY created_at DESC LIMIT 5000';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function setStatus(int $id, string $status, ?string $notes = null): void
    {
        if ($notes !== null) {
            $stmt = Database::pdo()->prepare('UPDATE leads SET status = ?, notes = ? WHERE id = ?');
            $stmt->execute([$status, $notes, $id]);
            return;
        }
        $stmt = Database::pdo()->prepare('UPDATE leads SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
}
