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
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'type' => (string) $row['type'],
                'full_name' => (string) $row['full_name'],
                'document' => self::maskDocument((string) $row['document']),
            ],
            $stmt->fetchAll()
        );
    }

    private static function maskDocument(string $document): string
    {
        $digits = preg_replace('/\D/', '', $document) ?? '';
        if (strlen($digits) <= 4) {
            return '****';
        }
        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(?int $createdBy = null): array
    {
        if ($createdBy === null) {
            return Database::query('SELECT * FROM customers ORDER BY full_name')->fetchAll();
        }
        $stmt = Database::prepare('SELECT * FROM customers WHERE created_by = ? ORDER BY full_name');
        $stmt->execute([$createdBy]);
        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $filters
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
        $stmt = Database::prepare('SELECT COUNT(*) FROM customers' . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $stmt = Database::prepare(
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

    /** @param array<string, mixed> $d */
    public static function create(array $d): int
    {
        $stmt = Database::prepare(
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

    /** @param array<string, mixed> $d */
    public static function update(int $id, array $d): void
    {
        $stmt = Database::prepare(
            'UPDATE customers SET type=?, full_name=?, document=?, email=?, phone=?, address=?, city=?, state=?, zip_code=?, notes=?, attachment_path=? WHERE id=?'
        );
        $stmt->execute([
            $d['type'], $d['full_name'], $d['document'], $d['email'] ?: null, $d['phone'],
            $d['address'] ?? null, $d['city'] ?? null, $d['state'] ?? null, $d['zip_code'] ?? null,
            $d['notes'] ?? null, $d['attachment_path'] ?? null, $id,
        ]);
    }

    /** @param array<string, mixed> $row */
    public static function isAnonymized(array $row): bool
    {
        if (!Schema::hasColumn('customers', 'anonymized_at')) {
            return false;
        }
        return !empty($row['anonymized_at']);
    }

    public static function hasActiveReservations(int $customerId): bool
    {
        $stmt = Database::prepare(
            "SELECT COUNT(*) FROM reservations WHERE customer_id = ? AND status IN ('pending','confirmed','active')"
        );
        $stmt->execute([$customerId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function anonymize(int $id): void
    {
        $doc = 'ANON-' . $id . '-' . strtoupper(bin2hex(random_bytes(4)));
        $name = 'Titular anonimizado #' . $id;
        $sql = 'UPDATE customers SET type=?, full_name=?, document=?, email=NULL, phone=?, address=NULL, city=NULL, state=NULL, zip_code=NULL, notes=NULL, attachment_path=NULL';
        if (Schema::hasColumn('customers', 'anonymized_at')) {
            $sql .= ', anonymized_at=NOW()';
        }
        $sql .= ' WHERE id=?';
        $stmt = Database::prepare($sql);
        $stmt->execute(['individual', $name, $doc, '00000000000', $id]);
    }
}
