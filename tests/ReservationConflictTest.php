<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReservationConflictTest extends TestCase
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

    public function testHasConflictDetectsOverlap(): void
    {
        $carId = $this->firstCarId();
        $customerId = $this->firstCustomerId();
        $locationId = $this->firstLocationId();
        $userId = $this->firstUserId();
        if ($carId === 0 || $customerId === 0) {
            self::markTestSkipped('Seed insuficiente');
        }

        $code = 'TEST-' . bin2hex(random_bytes(4));
        $stmt = self::$pdo->prepare(
            "INSERT INTO reservations (code, customer_id, car_id, operator_id, pickup_location_id, return_location_id,
             pickup_date, pickup_time, return_date, return_time, daily_rate, total_days, total_amount, discount, final_amount, status, payment_status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $code, $customerId, $carId, $userId, $locationId, $locationId,
            '2030-06-01', '09:00:00', '2030-06-05', '18:00:00',
            100, 5, 500, 0, 500, 'confirmed', 'unpaid',
        ]);
        $inserted = (int) self::$pdo->lastInsertId();

        self::assertTrue(Reservation::hasConflict($carId, '2030-06-03', '10:00:00', '2030-06-04', '12:00:00', null));
        self::assertFalse(Reservation::hasConflict($carId, '2030-06-06', '09:00:00', '2030-06-08', '18:00:00', null));

        self::$pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$inserted]);
    }

    public function testCreateSafelyRejectsConflict(): void
    {
        $carId = $this->firstCarId();
        $customerId = $this->firstCustomerId();
        $locationId = $this->firstLocationId();
        $userId = $this->firstUserId();
        if ($carId === 0 || $customerId === 0) {
            self::markTestSkipped('Seed insuficiente');
        }

        $base = [
            'code' => 'ATOM-' . bin2hex(random_bytes(4)),
            'customer_id' => $customerId,
            'car_id' => $carId,
            'operator_id' => $userId,
            'pickup_location_id' => $locationId,
            'return_location_id' => $locationId,
            'pickup_date' => '2031-01-10',
            'pickup_time' => '09:00:00',
            'return_date' => '2031-01-12',
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

        $id1 = Reservation::createSafely($base);
        self::assertGreaterThan(0, $id1);
        $base['code'] = 'ATOM-' . bin2hex(random_bytes(4));
        $this->expectException(ReservationConflictException::class);
        Reservation::createSafely($base);

        self::$pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$id1]);
    }

    private function firstCarId(): int
    {
        return (int) self::$pdo->query('SELECT id FROM cars WHERE deleted_at IS NULL LIMIT 1')->fetchColumn();
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
