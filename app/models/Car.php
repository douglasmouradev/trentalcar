<?php

declare(strict_types=1);

final class Car
{
    /**
     * @param array<string, scalar|null|array<int, int>> $filters
     * @return array{0: string, 1: array<int, mixed>}
     */
    private static function filterSql(array $filters): array
    {
        $sql = '';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND c.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $sql .= ' AND c.brand LIKE ?';
            $params[] = '%' . $filters['brand'] . '%';
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (c.model LIKE ? OR c.license_plate LIKE ? OR c.brand LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        if (array_key_exists('restrict_to_car_ids', $filters)) {
            $ids = $filters['restrict_to_car_ids'];
            if (!is_array($ids) || $ids === []) {
                $sql .= ' AND 1=0';
            } else {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql .= ' AND c.id IN (' . $placeholders . ')';
                foreach ($ids as $cid) {
                    $params[] = (int) $cid;
                }
            }
        }
        return [$sql, $params];
    }

    /** @param array<string, scalar|null|array<int, int>> $filters */
    public static function search(array $filters = []): array
    {
        [$frag, $params] = self::filterSql($filters);
        $sql = 'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE 1=1'
            . $frag . ' ORDER BY c.brand, c.model';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * @param array<string, scalar|null|array<int, int>> $filters
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function searchPaginated(array $filters, int $page, int $perPage): array
    {
        [$frag, $params] = self::filterSql($filters);
        $countSql = 'SELECT COUNT(*) FROM cars c WHERE 1=1' . $frag;
        $stmt = Database::pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE 1=1'
            . $frag . ' ORDER BY c.brand, c.model LIMIT ' . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
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
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Soma dos gastos mensais estimados (R$). */
    public static function monthlyExpensesTotal(array $c): float
    {
        return max(0.0, (float) ($c['monthly_fuel'] ?? 0))
            + max(0.0, (float) ($c['monthly_toll'] ?? 0))
            + max(0.0, (float) ($c['monthly_wash'] ?? 0))
            + max(0.0, (float) ($c['monthly_maintenance'] ?? 0))
            + max(0.0, (float) ($c['monthly_extra'] ?? 0));
    }

    public static function create(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO cars (license_plate, brand, model, year, color, color_hex, category, seats, transmission, fuel, daily_rate, status, location_id, mileage,
             monthly_fuel, monthly_toll, monthly_wash, monthly_maintenance, monthly_extra, image_url, notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['license_plate'], $d['brand'], $d['model'], (int) $d['year'], $d['color'],
            $d['color_hex'] ?? '#CCCCCC', $d['category'], (int) ($d['seats'] ?? 5),
            $d['transmission'], $d['fuel'], $d['daily_rate'], $d['status'],
            $d['location_id'] ?: null, (int) ($d['mileage'] ?? 0),
            $d['monthly_fuel'], $d['monthly_toll'], $d['monthly_wash'], $d['monthly_maintenance'], $d['monthly_extra'],
            $d['image_url'] ?? null, $d['notes'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE cars SET license_plate=?, brand=?, model=?, year=?, color=?, color_hex=?, category=?, seats=?, transmission=?, fuel=?, daily_rate=?, status=?, location_id=?, mileage=?,
             monthly_fuel=?, monthly_toll=?, monthly_wash=?, monthly_maintenance=?, monthly_extra=?, image_url=?, notes=? WHERE id=?'
        );
        $stmt->execute([
            $d['license_plate'], $d['brand'], $d['model'], (int) $d['year'], $d['color'],
            $d['color_hex'] ?? '#CCCCCC', $d['category'], (int) ($d['seats'] ?? 5),
            $d['transmission'], $d['fuel'], $d['daily_rate'], $d['status'],
            $d['location_id'] ?: null, (int) ($d['mileage'] ?? 0),
            $d['monthly_fuel'], $d['monthly_toll'], $d['monthly_wash'], $d['monthly_maintenance'], $d['monthly_extra'],
            $d['image_url'] ?? null, $d['notes'] ?? null, $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM cars WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function forPublicLanding(int $limit = 12): array
    {
        $sql = "SELECT c.*, l.name AS location_name FROM cars c
                LEFT JOIN locations l ON l.id = c.location_id
                WHERE c.status IN ('available','rented')
                ORDER BY c.daily_rate ASC, c.brand, c.model
                LIMIT " . (int) $limit;
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function landingFilterKey(string $category): string
    {
        return match ($category) {
            'economy' => 'economy',
            'suv' => 'suv',
            'luxury' => 'exec',
            'van', 'truck' => 'util',
            default => 'sedan',
        };
    }

    public static function isAvailableForDates(int $carId, string $start, string $end): bool
    {
        return !Reservation::hasConflict($carId, $start, '09:00:00', $end, '18:00:00', null);
    }
}
