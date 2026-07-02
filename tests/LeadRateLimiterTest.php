<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LeadRateLimiterTest extends TestCase
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
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testTooManyInitiallyFalse(): void
    {
        self::assertFalse(LeadRateLimiter::tooMany());
    }
}
