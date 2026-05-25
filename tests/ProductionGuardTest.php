<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductionGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['APP_ENV'], $_ENV['APP_DEBUG'], $_ENV['DB_PASSWORD']);
        parent::tearDown();
    }

    public function testIsProduction(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $this->assertTrue(ProductionGuard::isProduction());
        $_ENV['APP_ENV'] = 'development';
        $this->assertFalse(ProductionGuard::isProduction());
    }

    public function testValidateBootFailsWhenDebugOn(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['APP_URL'] = 'https://example.com';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_DATABASE'] = 'db';
        $_ENV['DB_USERNAME'] = 'u';
        $_ENV['DB_PASSWORD'] = 'secret';
        $_ENV['PRIVACY_DPO_EMAIL'] = 'dpo@test.com';
        $_ENV['SECURITY_CONTACT_EMAIL'] = 'sec@test.com';

        $this->expectException(RuntimeException::class);
        ProductionGuard::validateBoot();
    }
}
