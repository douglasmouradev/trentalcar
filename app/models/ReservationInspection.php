<?php

declare(strict_types=1);

final class ReservationInspection
{
    /** @return array<int, array<string, mixed>> */
    public static function forReservation(int $reservationId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT i.*, u.name AS created_by_name
             FROM reservation_inspections i
             LEFT JOIN users u ON u.id = i.created_by
             WHERE i.reservation_id = ?
             ORDER BY i.created_at ASC'
        );
        $stmt->execute([$reservationId]);
        return $stmt->fetchAll();
    }

    public static function create(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO reservation_inspections
             (reservation_id, kind, mileage, fuel_level, damage_notes, extra_charges, photo_path, created_by)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            (int) $d['reservation_id'],
            $d['kind'],
            $d['mileage'] ?? null,
            $d['fuel_level'] ?? null,
            $d['damage_notes'] ?? null,
            (float) ($d['extra_charges'] ?? 0),
            $d['photo_path'] ?? null,
            $d['created_by'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }
}
