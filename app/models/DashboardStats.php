<?php

declare(strict_types=1);

final class DashboardStats
{
    /**
     * @return array{
     *   revenueMonth: float,
     *   fleet: int,
     *   activeRes: int,
     *   occupancy: int,
     *   unpaid: int,
     *   chartDays: array<string, int>,
     *   revenueByCategory: array<int, array<string, mixed>>,
     *   returns: array<int, array<string, mixed>>,
     *   maintenance: array<int, array<string, mixed>>,
     *   myToday: array<int, array<string, mixed>>,
     *   myTodayCount: int
     * }
     */
    public static function ownerMetrics(int $operatorId, bool $isOwner): array
    {
        $pdo = Database::pdo();
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $revenueStmt = $pdo->prepare(
            "SELECT COALESCE(SUM(final_amount),0) FROM reservations
             WHERE status IN ('confirmed','active','completed') AND pickup_date BETWEEN ? AND ?"
        );
        $revenueStmt->execute([$monthStart, $monthEnd]);
        $revenueMonth = (float) $revenueStmt->fetchColumn();

        $fleet = (int) $pdo->query('SELECT COUNT(*) FROM cars WHERE deleted_at IS NULL')->fetchColumn();
        $activeRes = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'active'")->fetchColumn();
        $occupancy = $fleet > 0 ? (int) round(($activeRes / $fleet) * 100) : 0;

        $unpaid = (int) $pdo->query(
            "SELECT COUNT(*) FROM reservations WHERE payment_status = 'unpaid' AND status NOT IN ('cancelled','completed')"
        )->fetchColumn();

        $chartDays = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chartDays[$d] = 0;
        }
        $stmt = $pdo->query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM reservations
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at)"
        );
        foreach ($stmt->fetchAll() as $row) {
            if (isset($chartDays[$row['d']])) {
                $chartDays[$row['d']] = (int) $row['c'];
            }
        }

        $catQ = $pdo->prepare(
            "SELECT car.category, COALESCE(SUM(r.final_amount),0) AS total
             FROM reservations r JOIN cars car ON car.id = r.car_id AND car.deleted_at IS NULL
             WHERE r.status IN ('confirmed','active','completed') AND r.pickup_date BETWEEN ? AND ?
             GROUP BY car.category"
        );
        $catQ->execute([$monthStart, $monthEnd]);
        $revenueByCategory = $catQ->fetchAll();

        $returns = $pdo->query(
            "SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate
             FROM reservations r
             JOIN customers c ON c.id = r.customer_id
             JOIN cars car ON car.id = r.car_id
             WHERE r.status IN ('active','confirmed') AND r.return_date IN (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY))
             ORDER BY r.return_date, r.return_time LIMIT 15"
        )->fetchAll();

        $maintenance = $pdo->query(
            "SELECT * FROM cars WHERE status = 'maintenance' AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT 10"
        )->fetchAll();

        $myToday = [];
        $myTodayCount = 0;
        if (!$isOwner && $operatorId > 0) {
            $t = $pdo->prepare(
                "SELECT COUNT(*) FROM reservations WHERE operator_id = ? AND pickup_date = CURDATE() AND status NOT IN ('cancelled','completed')"
            );
            $t->execute([$operatorId]);
            $myTodayCount = (int) $t->fetchColumn();
            $list = $pdo->prepare(
                "SELECT r.*, c.full_name AS customer_name, car.brand, car.model
                 FROM reservations r
                 JOIN customers c ON c.id = r.customer_id
                 JOIN cars car ON car.id = r.car_id
                 WHERE r.operator_id = ? AND r.pickup_date >= CURDATE() AND r.status NOT IN ('cancelled','completed')
                 ORDER BY r.pickup_date LIMIT 10"
            );
            $list->execute([$operatorId]);
            $myToday = $list->fetchAll();
        }

        return [
            'revenueMonth' => $revenueMonth,
            'fleet' => $fleet,
            'activeRes' => $activeRes,
            'occupancy' => $occupancy,
            'unpaid' => $unpaid,
            'chartDays' => $chartDays,
            'revenueByCategory' => $revenueByCategory,
            'returns' => $returns,
            'maintenance' => $maintenance,
            'myToday' => $myToday,
            'myTodayCount' => $myTodayCount,
        ];
    }

    public static function partnerActiveReservations(array $carIds): int
    {
        if ($carIds === []) {
            return 0;
        }
        $ph = implode(',', array_fill(0, count($carIds), '?'));
        $st = Database::pdo()->prepare("SELECT COUNT(*) FROM reservations WHERE status = 'active' AND car_id IN ($ph)");
        $st->execute($carIds);
        return (int) $st->fetchColumn();
    }
}
