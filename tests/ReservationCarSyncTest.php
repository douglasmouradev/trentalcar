<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReservationCarSyncTest extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        if (($_ENV['DB_DATABASE'] ?? '') === '') {
            self::markTestSkipped('DB_DATABASE não configurado');
        }
        try {
            self::$pdo = Database::pdo();
        } catch (Throwable $e) {
            self::markTestSkipped('Base de dados indisponível: ' . $e->getMessage());
        }
    }

    public function testCancelReservationFreesCar(): void
    {
        $carId = $this->firstAvailableCarId();
        $customerId = $this->firstCustomerId();
        $locationId = $this->firstLocationId();
        $userId = $this->firstUserId();
        if ($carId === 0 || $customerId === 0) {
            self::markTestSkipped('Seed insuficiente');
        }

        self::$pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?")->execute([$carId]);

        $base = [
            'customer_id' => $customerId,
            'car_id' => $carId,
            'operator_id' => $userId,
            'pickup_location_id' => $locationId,
            'return_location_id' => $locationId,
            'pickup_date' => '2098-08-01',
            'pickup_time' => '09:00:00',
            'return_date' => '2098-08-03',
            'return_time' => '18:00:00',
            'daily_rate' => 100,
            'total_days' => 3,
            'total_amount' => 300,
            'discount' => 0,
            'final_amount' => 300,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => null,
            'notes' => null,
        ];

        $id = Reservation::createSafely($base);
        self::assertSame('rented', $this->carStatus($carId));

        Reservation::setStatus($id, 'cancelled');
        self::assertSame('available', $this->carStatus($carId));

        self::$pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$id]);
    }

    private function carStatus(int $carId): string
    {
        $stmt = self::$pdo->prepare('SELECT status FROM cars WHERE id = ?');
        $stmt->execute([$carId]);

        return (string) $stmt->fetchColumn();
    }

    private function firstAvailableCarId(): int
    {
        $sql = "SELECT c.id FROM cars c
                WHERE c.deleted_at IS NULL
                  AND NOT EXISTS (
                    SELECT 1 FROM reservations r
                    WHERE r.car_id = c.id AND r.status IN ('pending','confirmed','active')
                  )
                LIMIT 1";

        return (int) self::$pdo->query($sql)->fetchColumn();
    }

    private function firstCustomerId(): int
    {
        return (int) self::$pdo->query('SELECT id FROM customers LIMIT 1')->fetchColumn();
    }

    private function firstLocationId(): int
    {
        return (int) self::$pdo->query('SELECT id FROM locations LIMIT 1')->fetchColumn();
    }

    private function firstUserId(): int
    {
        return (int) self::$pdo->query('SELECT id FROM users LIMIT 1')->fetchColumn();
    }
}
