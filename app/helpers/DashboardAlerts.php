<?php

declare(strict_types=1);

final class DashboardAlerts
{
    /**
     * @return array{
     *   overdue: array<int, array<string, mixed>>,
     *   checkins_today: array<int, array<string, mixed>>,
     *   unpaid: array<int, array<string, mixed>>,
     *   new_leads: array<int, array<string, mixed>>,
     *   stale_leads_count: int,
     *   new_leads_count: int
     * }
     */
    public static function collect(PDO $pdo, bool $isOwner, ?int $operatorId): array
    {
        $opFilter = '';
        $params = [];
        if (!$isOwner && $operatorId !== null) {
            $opFilter = ' AND r.operator_id = ?';
            $params[] = $operatorId;
        }

        $overdueSql = "SELECT r.id, r.code, r.return_date, c.full_name AS customer_name, car.brand, car.model
            FROM reservations r
            JOIN customers c ON c.id = r.customer_id
            JOIN cars car ON car.id = r.car_id
            WHERE r.status = 'active' AND r.return_date < CURDATE(){$opFilter}
            ORDER BY r.return_date ASC LIMIT 10";
        $stmt = Database::prepare($overdueSql);
        $stmt->execute($params);
        $overdue = $stmt->fetchAll();

        $checkinSql = "SELECT r.id, r.code, r.pickup_time, c.full_name AS customer_name, car.brand, car.model
            FROM reservations r
            JOIN customers c ON c.id = r.customer_id
            JOIN cars car ON car.id = r.car_id
            WHERE r.status IN ('pending','confirmed') AND r.pickup_date = CURDATE(){$opFilter}
            ORDER BY r.pickup_time ASC LIMIT 10";
        $stmt = Database::prepare($checkinSql);
        $stmt->execute($params);
        $checkins = $stmt->fetchAll();

        $unpaidSql = "SELECT r.id, r.code, r.final_amount, c.full_name AS customer_name
            FROM reservations r
            JOIN customers c ON c.id = r.customer_id
            WHERE r.payment_status IN ('unpaid','partial') AND r.status NOT IN ('cancelled','completed'){$opFilter}
            ORDER BY r.pickup_date ASC LIMIT 10";
        $stmt = Database::prepare($unpaidSql);
        $stmt->execute($params);
        $unpaid = $stmt->fetchAll();

        $newLeads = ($isOwner || Auth::isOperator()) ? Lead::recentNew(5) : [];

        return [
            'overdue' => $overdue,
            'checkins_today' => $checkins,
            'unpaid' => $unpaid,
            'new_leads' => $newLeads,
            'stale_leads_count' => ($isOwner || Auth::isOperator()) ? Lead::countStale(24) : 0,
            'new_leads_count' => ($isOwner || Auth::isOperator()) ? Lead::countNew() : 0,
        ];
    }
}
