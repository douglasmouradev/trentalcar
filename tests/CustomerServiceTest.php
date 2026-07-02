<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CustomerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        if (trim((string) ($_ENV['DB_DATABASE'] ?? '')) === '') {
            self::markTestSkipped('DB_DATABASE não configurado');
        }
        try {
            Database::pdo()->query('SELECT 1');
        } catch (Throwable $e) {
            self::markTestSkipped('Base de dados indisponível: ' . $e->getMessage());
        }
    }

    public function testExportPayloadNullWhenMissing(): void
    {
        $this->assertNull(CustomerService::exportPayload(999999999));
    }

    public function testAnonymizeRemovesPiiWhenNoActiveReservations(): void
    {
        if (!Schema::hasColumn('customers', 'anonymized_at')) {
            self::markTestSkipped('Coluna anonymized_at ausente');
        }

        $id = Customer::create([
            'type' => 'individual',
            'full_name' => 'Cliente PHPUnit LGPD',
            'document' => '52998224725',
            'email' => 'lgpd-' . bin2hex(random_bytes(4)) . '@example.com',
            'phone' => '11977776666',
            'created_by' => null,
        ]);
        $this->assertGreaterThan(0, $id);

        $result = CustomerService::anonymize($id);
        $this->assertTrue($result['ok']);
        $this->assertNull($result['error']);

        $row = Customer::find($id);
        $this->assertNotNull($row);
        $this->assertTrue(Customer::isAnonymized($row));
        $this->assertNull($row['email']);
        $this->assertStringContainsString('anonimizado', strtolower((string) $row['full_name']));

        Database::pdo()->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
    }

    public function testAnonymizeBlockedWhenActiveReservationExists(): void
    {
        if (!Schema::hasColumn('customers', 'anonymized_at')) {
            self::markTestSkipped('Coluna anonymized_at ausente');
        }

        $customerId = (int) Database::pdo()->query(
            "SELECT customer_id FROM reservations WHERE status IN ('pending','confirmed','active') LIMIT 1"
        )->fetchColumn();
        if ($customerId < 1) {
            self::markTestSkipped('Seed sem reserva activa');
        }

        $result = CustomerService::anonymize($customerId);
        $this->assertFalse($result['ok']);
        $this->assertSame('active_reservations', $result['error']);
    }

    public function testAnonymizeFailsWhenAlreadyAnonymized(): void
    {
        if (!Schema::hasColumn('customers', 'anonymized_at')) {
            self::markTestSkipped('Coluna anonymized_at ausente');
        }

        $id = Customer::create([
            'type' => 'individual',
            'full_name' => 'Cliente Duplo Anon',
            'document' => '39053344705',
            'email' => 'lgpd2-' . bin2hex(random_bytes(4)) . '@example.com',
            'phone' => '11966665555',
            'created_by' => null,
        ]);
        CustomerService::anonymize($id);
        $again = CustomerService::anonymize($id);
        $this->assertFalse($again['ok']);
        $this->assertSame('already_anonymized', $again['error']);

        Database::pdo()->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
    }
}
