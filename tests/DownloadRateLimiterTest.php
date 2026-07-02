<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DownloadRateLimiterTest extends TestCase
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

    public function testInitiallyNotBlocked(): void
    {
        $uid = 999001;
        DbRateLimiter::clear('download:user:' . $uid);
        $this->assertFalse(DownloadRateLimiter::tooMany($uid));
    }
}
