<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuthLoginIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        if (($_ENV['DB_DATABASE'] ?? '') === '') {
            self::markTestSkipped('DB_DATABASE não configurado');
        }
        try {
            Database::pdo()->query('SELECT 1');
        } catch (Throwable $e) {
            self::markTestSkipped('Base de dados indisponível: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testOwnerCredentialsFromSeed(): void
    {
        $user = User::findByEmail('owner@titaniumrental.com');
        if ($user === null) {
            self::markTestSkipped('Utilizador owner do seed não encontrado');
        }
        $this->assertTrue((int) $user['is_active'] === 1);
        $this->assertTrue(password_verify('password123', (string) $user['password_hash']));
        Auth::login($user);
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::isOwner());
        $this->assertSame((int) $user['id'], Auth::id());
    }

    public function testCreateReservationAfterLogin(): void
    {
        $user = User::findByEmail('owner@titaniumrental.com');
        if ($user === null) {
            self::markTestSkipped('Seed insuficiente');
        }
        Auth::login($user);
        $carId = (int) Database::pdo()->query('SELECT id FROM cars WHERE deleted_at IS NULL LIMIT 1')->fetchColumn();
        $customerId = (int) Database::pdo()->query('SELECT id FROM customers LIMIT 1')->fetchColumn();
        $locationId = (int) Database::pdo()->query('SELECT id FROM locations LIMIT 1')->fetchColumn();
        if ($carId < 1 || $customerId < 1 || $locationId < 1) {
            self::markTestSkipped('Dados de frota/cliente/local em falta');
        }
        $code = 'TST-' . bin2hex(random_bytes(3));
        $id = Reservation::create([
            'code' => $code,
            'customer_id' => $customerId,
            'car_id' => $carId,
            'operator_id' => (int) $user['id'],
            'pickup_location_id' => $locationId,
            'return_location_id' => $locationId,
            'pickup_date' => '2099-06-01',
            'pickup_time' => '10:00:00',
            'return_date' => '2099-06-05',
            'return_time' => '10:00:00',
            'daily_rate' => 100.0,
            'total_days' => 4,
            'total_amount' => 400.0,
            'discount' => 0.0,
            'final_amount' => 400.0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => null,
            'notes' => 'PHPUnit integration',
        ]);
        $this->assertGreaterThan(0, $id);
        $row = Reservation::find($id);
        $this->assertNotNull($row);
        $this->assertSame($code, $row['code']);
        Database::pdo()->prepare('DELETE FROM reservations WHERE id = ?')->execute([$id]);
    }
}
