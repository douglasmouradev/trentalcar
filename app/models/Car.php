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
        if (!empty($filters['brand']) && is_string($filters['brand'])) {
            $sql .= ' AND c.brand LIKE ?';
            $params[] = '%' . $filters['brand'] . '%';
        }
        if (!empty($filters['q']) && is_string($filters['q'])) {
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
        if (self::supportsSoftDelete()) {
            $sql .= ' AND c.deleted_at IS NULL';
        }
        return [$sql, $params];
    }

    private static function supportsSoftDelete(): bool
    {
        return Schema::hasColumn('cars', 'deleted_at');
    }

    /**
     * @param array<string, scalar|null|array<int, int>> $filters
     * @return array<int, array<string, mixed>>
     */
    public static function search(array $filters = []): array
    {
        [$frag, $params] = self::filterSql($filters);
        $sql = 'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE 1=1'
            . $frag . ' ORDER BY c.brand, c.model';
        $stmt = Database::prepare($sql);
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
        $stmt = Database::prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE 1=1'
            . $frag . ' ORDER BY c.brand, c.model LIMIT ' . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
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

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $sql = 'SELECT c.*, l.name AS location_name FROM cars c LEFT JOIN locations l ON l.id = c.location_id WHERE c.id = ?';
        $stmt = Database::prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function activeReservationCount(int $carId): int
    {
        $stmt = Database::prepare(
            "SELECT COUNT(*) FROM reservations WHERE car_id = ? AND status IN ('pending','confirmed','active')"
        );
        $stmt->execute([$carId]);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<string> */
    public static function monthlyExpenseFields(): array
    {
        return [
            'monthly_insurance',
            'monthly_document',
            'monthly_ipva',
            'monthly_wash',
            'monthly_site_rent',
            'monthly_internet',
            'monthly_water',
            'monthly_electricity',
            'monthly_phone',
            'monthly_staff',
            'monthly_tag_annual',
            'monthly_fuel',
            'monthly_toll',
            'monthly_maintenance',
            'monthly_extra',
        ];
    }

    /**
     * Soma dos gastos mensais estimados (R$).
     * @param array<string, mixed> $c
     */
    public static function monthlyExpensesTotal(array $c): float
    {
        $total = 0.0;
        foreach (self::monthlyExpenseFields() as $field) {
            $total += max(0.0, (float) ($c[$field] ?? 0));
        }
        return $total;
    }

    /**
     * Frota ativa com campos de custo mensal, ordenada pelo total estimado (desc).
     * @return list<array<string, mixed>>
     */
    public static function allWithMonthlyExpenses(): array
    {
        $deleted = self::supportsSoftDelete() ? ' AND c.deleted_at IS NULL' : '';
        $sql = "SELECT c.*, l.name AS location_name FROM cars c
                LEFT JOIN locations l ON l.id = c.location_id
                WHERE 1=1{$deleted}
                ORDER BY c.brand, c.model, c.license_plate";
        $rows = Database::query($sql)->fetchAll();
        usort($rows, static function (array $a, array $b): int {
            $tb = self::monthlyExpensesTotal($b);
            $ta = self::monthlyExpensesTotal($a);
            if ($tb === $ta) {
                return strcasecmp(
                    trim((string) ($a['brand'] ?? '') . ' ' . (string) ($a['model'] ?? '')),
                    trim((string) ($b['brand'] ?? '') . ' ' . (string) ($b['model'] ?? ''))
                );
            }
            return $tb <=> $ta;
        });
        return $rows;
    }

    /**
     * Soma por categoria de gasto mensal na frota.
     * @param list<array<string, mixed>> $cars
     * @return array<string, float>
     */
    public static function monthlyExpenseCategoryTotals(array $cars): array
    {
        $totals = [];
        foreach (self::monthlyExpenseFields() as $field) {
            $totals[$field] = 0.0;
        }
        foreach ($cars as $car) {
            foreach (self::monthlyExpenseFields() as $field) {
                $totals[$field] += max(0.0, (float) ($car[$field] ?? 0));
            }
        }
        return $totals;
    }

    /** @param array<string, mixed> $d */
    public static function create(array $d): int
    {
        $monthlyCols = implode(', ', self::monthlyExpenseFields());
        $monthlyPlaceholders = implode(', ', array_fill(0, count(self::monthlyExpenseFields()), '?'));
        $stmt = Database::prepare(
            "INSERT INTO cars (license_plate, brand, model, year, color, color_hex, category, seats, transmission, fuel, daily_rate, status, location_id, mileage,
             {$monthlyCols}, image_url, notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,{$monthlyPlaceholders},?,?)"
        );
        $params = [
            $d['license_plate'], $d['brand'], $d['model'], (int) $d['year'], $d['color'],
            $d['color_hex'] ?? '#CCCCCC', $d['category'], (int) ($d['seats'] ?? 5),
            $d['transmission'], $d['fuel'], $d['daily_rate'], $d['status'],
            $d['location_id'] ?: null, (int) ($d['mileage'] ?? 0),
        ];
        foreach (self::monthlyExpenseFields() as $field) {
            $params[] = max(0.0, (float) ($d[$field] ?? 0));
        }
        $params[] = $d['image_url'] ?? null;
        $params[] = $d['notes'] ?? null;
        $stmt->execute($params);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $d */
    public static function update(int $id, array $d): void
    {
        $monthlySet = implode(', ', array_map(static fn (string $f): string => "{$f}=?", self::monthlyExpenseFields()));
        $stmt = Database::prepare(
            "UPDATE cars SET license_plate=?, brand=?, model=?, year=?, color=?, color_hex=?, category=?, seats=?, transmission=?, fuel=?, daily_rate=?, status=?, location_id=?, mileage=?,
             {$monthlySet}, image_url=?, notes=? WHERE id=?"
        );
        $params = [
            $d['license_plate'], $d['brand'], $d['model'], (int) $d['year'], $d['color'],
            $d['color_hex'] ?? '#CCCCCC', $d['category'], (int) ($d['seats'] ?? 5),
            $d['transmission'], $d['fuel'], $d['daily_rate'], $d['status'],
            $d['location_id'] ?: null, (int) ($d['mileage'] ?? 0),
        ];
        foreach (self::monthlyExpenseFields() as $field) {
            $params[] = max(0.0, (float) ($d[$field] ?? 0));
        }
        $params[] = $d['image_url'] ?? null;
        $params[] = $d['notes'] ?? null;
        $params[] = $id;
        $stmt->execute($params);
    }

    /**
     * Atualiza apenas os gastos mensais estimados do veículo.
     * @param array<string, float|int|string> $amounts
     */
    public static function updateMonthlyExpenses(int $id, array $amounts): void
    {
        $monthlySet = implode(', ', array_map(static fn (string $f): string => "{$f}=?", self::monthlyExpenseFields()));
        $stmt = Database::prepare("UPDATE cars SET {$monthlySet} WHERE id=?");
        $params = [];
        foreach (self::monthlyExpenseFields() as $field) {
            $params[] = max(0.0, (float) str_replace(',', '.', (string) ($amounts[$field] ?? '0')));
        }
        $params[] = $id;
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        if (self::activeReservationCount($id) > 0) {
            throw new RuntimeException('car_has_active_reservations');
        }
        if (self::supportsSoftDelete()) {
            $stmt = Database::prepare('UPDATE cars SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            return;
        }
        $stmt = Database::prepare('DELETE FROM cars WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function forPublicLanding(?int $limit = null): array
    {
        $deleted = self::supportsSoftDelete() ? ' AND c.deleted_at IS NULL' : '';
        $sql = "SELECT c.*, l.name AS location_name FROM cars c
                LEFT JOIN locations l ON l.id = c.location_id
                WHERE c.status IN ('available','rented'){$deleted}
                ORDER BY c.daily_rate ASC, c.brand, c.model";
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return Database::query($sql)->fetchAll();
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

    /** URL pública da foto do veículo ou logo da marca. */
    public static function publicImageUrl(?string $imageUrl): string
    {
        $path = trim((string) $imageUrl);
        if ($path !== '') {
            return Router::url('/assets/uploads/' . rawurlencode(basename($path)));
        }
        return Router::url('/assets/img/logo.png');
    }

    public static function isAvailableForDates(int $carId, string $start, string $end): bool
    {
        return !Reservation::hasConflict($carId, $start, '09:00:00', $end, '18:00:00', null);
    }
}
