<?php

declare(strict_types=1);

final class UserCar
{
    /** @return array<int, int> */
    public static function carIdsForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare('SELECT car_id FROM user_cars WHERE user_id = ? ORDER BY car_id');
        $stmt->execute([$userId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $out[] = (int) $id;
        }
        return $out;
    }

    /** @return array<int, array<string, mixed>> */
    public static function assignmentsForUser(int $userId): array
    {
        $quotaCol = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00 AS quota_percent';
        $sql = "SELECT uc.car_id, {$quotaCol}, c.brand, c.model, c.license_plate, c.status, c.color_hex
                FROM user_cars uc
                JOIN cars c ON c.id = uc.car_id
                WHERE uc.user_id = ?
                ORDER BY c.brand, c.model";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function quotaForUserCar(int $userId, int $carId): float
    {
        if (!self::hasQuotaColumn()) {
            return in_array($carId, self::carIdsForUser($userId), true) ? 100.0 : 0.0;
        }
        $stmt = Database::pdo()->prepare('SELECT quota_percent FROM user_cars WHERE user_id = ? AND car_id = ? LIMIT 1');
        $stmt->execute([$userId, $carId]);
        $v = $stmt->fetchColumn();
        return $v !== false ? (float) $v : 0.0;
    }

    /** @return array<int, array<string, mixed>> — apenas para o dono */
    public static function partnersForCar(int $carId): array
    {
        $quotaCol = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00 AS quota_percent';
        $sql = "SELECT u.id, u.name, u.email, {$quotaCol}
                FROM user_cars uc
                JOIN users u ON u.id = uc.user_id
                WHERE uc.car_id = ? AND u.role = 'partner'
                ORDER BY u.name";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$carId]);
        return $stmt->fetchAll();
    }

    /**
     * @param array<int, array{car_id: int, quota: float}> $items
     */
    public static function syncWithQuotas(int $userId, array $items): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM user_cars WHERE user_id = ?')->execute([$userId]);
        if ($items === []) {
            return;
        }
        $hasQuota = self::hasQuotaColumn();
        $ins = $hasQuota
            ? $pdo->prepare('INSERT INTO user_cars (user_id, car_id, quota_percent) VALUES (?, ?, ?)')
            : $pdo->prepare('INSERT INTO user_cars (user_id, car_id) VALUES (?, ?)');
        $validIds = self::filterValidCarIds(array_map(static fn (array $item): int => (int) ($item['car_id'] ?? 0), $items));
        foreach ($items as $item) {
            $carId = (int) ($item['car_id'] ?? 0);
            if ($carId <= 0 || !isset($validIds[$carId])) {
                continue;
            }
            $quota = max(0.01, min(100.0, (float) ($item['quota'] ?? 100)));
            if ($hasQuota) {
                $ins->execute([$userId, $carId, $quota]);
            } else {
                $ins->execute([$userId, $carId]);
            }
        }
    }

    /** @param array<int, int> $carIds @return array<int, true> */
    private static function filterValidCarIds(array $carIds): array
    {
        $carIds = array_values(array_unique(array_filter($carIds, static fn (int $id): bool => $id > 0)));
        if ($carIds === []) {
            return [];
        }
        $ph = implode(',', array_fill(0, count($carIds), '?'));
        $stmt = Database::pdo()->prepare("SELECT id FROM cars WHERE id IN ($ph)");
        $stmt->execute($carIds);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $out[(int) $id] = true;
        }
        return $out;
    }

    /** @param array<int, int> $userIds @return array<int, int> */
    public static function carCountsForUsers(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter($userIds, static fn (int $id): bool => $id > 0)));
        if ($userIds === []) {
            return [];
        }
        $ph = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = Database::pdo()->prepare(
            "SELECT user_id, COUNT(*) AS c FROM user_cars WHERE user_id IN ($ph) GROUP BY user_id"
        );
        $stmt->execute($userIds);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int) $row['user_id']] = (int) $row['c'];
        }
        return $out;
    }

    public static function syncForUser(int $userId, array $carIds): void
    {
        $items = [];
        foreach ($carIds as $id) {
            $cid = (int) $id;
            if ($cid > 0) {
                $items[] = ['car_id' => $cid, 'quota' => 100.0];
            }
        }
        self::syncWithQuotas($userId, $items);
    }

    public static function deleteForUser(int $userId): void
    {
        Database::pdo()->prepare('DELETE FROM user_cars WHERE user_id = ?')->execute([$userId]);
    }

    /** Faturamento proporcional à cota do cotista no mês corrente. */
    public static function revenueShareMonth(int $userId, ?string $monthStart = null, ?string $monthEnd = null): float
    {
        $monthStart ??= date('Y-m-01');
        $monthEnd ??= date('Y-m-t');
        $quotaExpr = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00';
        $sql = "SELECT COALESCE(SUM(r.final_amount * ({$quotaExpr} / 100)), 0)
                FROM user_cars uc
                JOIN reservations r ON r.car_id = uc.car_id
                WHERE uc.user_id = ?
                  AND r.status IN ('confirmed','active','completed')
                  AND r.pickup_date BETWEEN ? AND ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$userId, $monthStart, $monthEnd]);
        return (float) $stmt->fetchColumn();
    }

    /** @return array<int, array<string, mixed>> */
    public static function revenueShareByCar(int $userId, ?string $monthStart = null, ?string $monthEnd = null): array
    {
        $monthStart ??= date('Y-m-01');
        $monthEnd ??= date('Y-m-t');
        $quotaExpr = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00';
        $sql = "SELECT uc.car_id, c.brand, c.model, c.license_plate, {$quotaExpr} AS quota_percent,
                       COALESCE(SUM(r.final_amount * ({$quotaExpr} / 100)), 0) AS revenue_share
                FROM user_cars uc
                JOIN cars c ON c.id = uc.car_id
                LEFT JOIN reservations r ON r.car_id = uc.car_id
                    AND r.status IN ('confirmed','active','completed')
                    AND r.pickup_date BETWEEN ? AND ?
                WHERE uc.user_id = ?
                GROUP BY uc.car_id, c.brand, c.model, c.license_plate, {$quotaExpr}
                ORDER BY c.brand, c.model";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$monthStart, $monthEnd, $userId]);
        return $stmt->fetchAll();
    }

    /** @return array<int, array{month: string, revenue: float}> */
    public static function revenueByMonth(int $userId, int $months = 6): array
    {
        $out = [];
        $quotaExpr = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00';
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = date('Y-m-01', strtotime("-{$i} months"));
            $end = date('Y-m-t', strtotime($start));
            $label = date('Y-m', strtotime($start));
            $sql = "SELECT COALESCE(SUM(r.final_amount * ({$quotaExpr} / 100)), 0)
                    FROM user_cars uc
                    JOIN reservations r ON r.car_id = uc.car_id
                    WHERE uc.user_id = ?
                      AND r.status IN ('confirmed','active','completed')
                      AND r.pickup_date BETWEEN ? AND ?";
            $stmt = Database::pdo()->prepare($sql);
            $stmt->execute([$userId, $start, $end]);
            $out[] = ['month' => $label, 'revenue' => (float) $stmt->fetchColumn()];
        }
        return $out;
    }

    /** @return array<int, array<string, mixed>> */
    public static function reservationsForPartner(int $userId, int $limit = 30): array
    {
        $quotaExpr = self::hasQuotaColumn() ? 'uc.quota_percent' : '100.00';
        $sql = "SELECT r.id, r.code, r.pickup_date, r.return_date, r.status, r.final_amount,
                       (r.final_amount * ({$quotaExpr} / 100)) AS share_amount,
                       c.brand, c.model, c.license_plate, cu.full_name AS customer_name
                FROM user_cars uc
                JOIN reservations r ON r.car_id = uc.car_id
                JOIN cars c ON c.id = uc.car_id
                JOIN customers cu ON cu.id = r.customer_id
                WHERE uc.user_id = ?
                ORDER BY r.pickup_date DESC
                LIMIT " . (int) $limit;
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private static function hasQuotaColumn(): bool
    {
        return Schema::hasColumn('user_cars', 'quota_percent');
    }
}
