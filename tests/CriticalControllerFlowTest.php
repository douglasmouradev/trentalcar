<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Fluxos críticos espelhados pelos controllers de leads e reservas. */
final class CriticalControllerFlowTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        self::bootDatabaseOrSkip();
    }

    private static function bootDatabaseOrSkip(): void
    {
        if (trim((string) ($_ENV['DB_DATABASE'] ?? '')) === '') {
            self::markTestSkipped('DB_DATABASE não configurado');
        }
        try {
            $c = Config::database();
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $c['host'],
                $c['port'],
                $c['database'],
                $c['charset']
            );
            $pdo = new PDO($dsn, $c['username'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $prop = new ReflectionProperty(Database::class, 'pdo');
            $prop->setValue(null, $pdo);
            $pdo->query('SELECT 1');
        } catch (Throwable $e) {
            self::markTestSkipped('Base de dados indisponível: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $prop = new ReflectionProperty(Database::class, 'pdo');
        $prop->setValue(null, null);
        parent::tearDown();
    }

    public function testLeadStaysNewUntilReservationMarksConverted(): void
    {
        $leadId = Lead::create([
            'full_name' => 'PHPUnit Lead',
            'email' => 'phpunit-lead-' . bin2hex(random_bytes(4)) . '@example.com',
            'phone' => '11988887777',
            'local' => 'Aeroporto',
            'inicio' => '2099-08-01',
            'fim' => '2099-08-05',
            'mesmo_local' => 1,
            'local_devolucao' => 'Aeroporto',
            'car_id' => null,
            'ip_hash' => hash('sha256', 'phpunit'),
        ]);
        $this->assertGreaterThan(0, $leadId);
        $this->assertSame('new', Lead::find($leadId)['status']);

        $_SESSION['lead_convert'] = [
            'lead_id' => $leadId,
            'customer_name' => 'PHPUnit Lead',
            'customer_email' => 'lead@example.com',
            'customer_phone' => '11988887777',
            'pickup_date' => '2099-08-01',
            'return_date' => '2099-08-05',
            'car_id' => null,
            'notes' => 'Lead convert session',
        ];
        $this->assertSame('new', Lead::find($leadId)['status']);

        Lead::updateStatus($leadId, 'converted', null);
        $this->assertSame('converted', Lead::find($leadId)['status']);

        Database::pdo()->prepare('DELETE FROM leads WHERE id = ?')->execute([$leadId]);
    }

    public function testCheckInPersistsInspectionInSameTransaction(): void
    {
        $user = User::findByEmail('owner@titaniumrental.com');
        if ($user === null) {
            self::markTestSkipped('Seed insuficiente');
        }
        if (!Schema::hasTable('reservation_inspections')) {
            self::markTestSkipped('Tabela reservation_inspections ausente');
        }

        $pdo = Database::pdo();
        $carId = (int) $pdo->query('SELECT id FROM cars WHERE deleted_at IS NULL LIMIT 1')->fetchColumn();
        $customerId = (int) $pdo->query('SELECT id FROM customers LIMIT 1')->fetchColumn();
        $locationId = (int) $pdo->query('SELECT id FROM locations LIMIT 1')->fetchColumn();
        if ($carId < 1 || $customerId < 1 || $locationId < 1) {
            self::markTestSkipped('Dados de frota/cliente/local em falta');
        }

        $code = 'CHK-' . bin2hex(random_bytes(3));
        $reservationId = Reservation::create([
            'code' => $code,
            'customer_id' => $customerId,
            'car_id' => $carId,
            'operator_id' => (int) $user['id'],
            'pickup_location_id' => $locationId,
            'return_location_id' => $locationId,
            'pickup_date' => '2099-09-01',
            'pickup_time' => '10:00:00',
            'return_date' => '2099-09-05',
            'return_time' => '10:00:00',
            'daily_rate' => 120.0,
            'total_days' => 4,
            'total_amount' => 480.0,
            'discount' => 0.0,
            'final_amount' => 480.0,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => null,
            'notes' => 'PHPUnit check-in',
        ]);
        $this->assertGreaterThan(0, $reservationId);

        Reservation::checkIn($reservationId, 45000, 'full', [
            'damage_notes' => 'PHPUnit inspection',
            'extra_charges' => 0,
            'photo_path' => null,
            'created_by' => (int) $user['id'],
        ]);

        $row = Reservation::find($reservationId);
        $this->assertNotNull($row);
        $this->assertSame('active', $row['status']);
        $this->assertSame(45000, (int) $row['pickup_mileage']);

        $inspections = ReservationInspection::forReservation($reservationId);
        $this->assertCount(1, $inspections);
        $this->assertSame('pickup', $inspections[0]['kind']);
        $this->assertSame('PHPUnit inspection', $inspections[0]['damage_notes']);

        $pdo->prepare('DELETE FROM reservation_inspections WHERE reservation_id = ?')->execute([$reservationId]);
        $pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$reservationId]);
        $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?")->execute([$carId]);
    }
}
