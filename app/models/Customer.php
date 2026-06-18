<?php

declare(strict_types=1);

final class Customer
{
    /** @return array<int, array<string, mixed>> */
    public static function searchAutocomplete(string $q, int $limit = 20, ?int $createdBy = null): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $like = '%' . $q . '%';
        $sql = 'SELECT id, type, full_name, document, email, phone FROM customers
             WHERE (full_name LIKE ? OR document LIKE ? OR email LIKE ?)';
        $params = [$like, $like, $like];
        if ($createdBy !== null) {
            $sql .= ' AND created_by = ?';
            $params[] = $createdBy;
        }
        $sql .= ' ORDER BY full_name LIMIT ' . (int) $limit;
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(?int $createdBy = null): array
    {
        if ($createdBy === null) {
            return Database::pdo()->query('SELECT * FROM customers ORDER BY full_name')->fetchAll();
        }
        $stmt = Database::pdo()->prepare('SELECT * FROM customers WHERE created_by = ? ORDER BY full_name');
        $stmt->execute([$createdBy]);
        return $stmt->fetchAll();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginated(int $page, int $perPage, ?int $createdBy = null, array $filters = []): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        if ($createdBy !== null) {
            $where .= ' AND created_by = ?';
            $params[] = $createdBy;
        }
        if (!empty($filters['type']) && in_array($filters['type'], ['individual', 'company'], true)) {
            $where .= ' AND type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['q'])) {
            $like = '%' . trim((string) $filters['q']) . '%';
            $where .= ' AND (full_name LIKE ? OR document LIKE ? OR email LIKE ? OR phone LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM customers' . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM customers' . $where . ' ORDER BY full_name LIMIT '
            . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset']
        );
        $stmt->execute($params);
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    public static function create(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO customers (type, full_name, document, email, phone, address, city, state, zip_code, notes, attachment_path, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['type'], $d['full_name'], $d['document'], $d['email'] ?: null, $d['phone'],
            $d['address'] ?? null, $d['city'] ?? null, $d['state'] ?? null, $d['zip_code'] ?? null,
            $d['notes'] ?? null, $d['attachment_path'] ?? null, $d['created_by'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE customers SET type=?, full_name=?, document=?, email=?, phone=?, address=?, city=?, state=?, zip_code=?, notes=?, attachment_path=? WHERE id=?'
        );
        $stmt->execute([
            $d['type'], $d['full_name'], $d['document'], $d['email'] ?: null, $d['phone'],
            $d['address'] ?? null, $d['city'] ?? null, $d['state'] ?? null, $d['zip_code'] ?? null,
            $d['notes'] ?? null, $d['attachment_path'] ?? null, $id,
        ]);
    }
}
