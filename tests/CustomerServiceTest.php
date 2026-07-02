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
}
