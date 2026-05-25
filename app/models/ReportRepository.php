<?php

declare(strict_types=1);

final class ReportRepository
{
    public static function validateDateRange(string $from, string $to): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            return false;
        }
        return strcmp($from, $to) <= 0;
    }

    /**
     * @return array{from: string, to: string}
     */
    public static function normalizeRange(?string $from, ?string $to): array
    {
        $from = is_string($from) ? trim($from) : '';
        $to = is_string($to) ? trim($to) : '';
        if (!self::validateDateRange($from, $to)) {
            return ['from' => date('Y-m-01'), 'to' => date('Y-m-t')];
        }
        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function monthlyRevenue(string $from, string $to): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT DATE_FORMAT(r.pickup_date, '%Y-%m') AS ym, SUM(r.final_amount) AS total, COUNT(*) AS cnt
             FROM reservations r
             WHERE r.status IN ('confirmed','active','completed') AND r.pickup_date BETWEEN ? AND ?
             GROUP BY ym ORDER BY ym"
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function fleetByStatus(): array
    {
        return Database::pdo()->query(
            'SELECT status, COUNT(*) AS c FROM cars WHERE deleted_at IS NULL GROUP BY status'
        )->fetchAll();
    }
}
