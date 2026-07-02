<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductionGuardTest extends TestCase
{
    /** @var array<string, string|null> */
    private array $envBackup = [];

    protected function setUp(): void
    {
        foreach ([
            'APP_ENV', 'APP_DEBUG', 'DB_PASSWORD', 'APP_KEY', 'HEALTH_TOKEN',
            'SESSION_SECURE', 'PRIVACY_DPO_EMAIL', 'SECURITY_CONTACT_EMAIL',
            'APP_URL', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME',
        ] as $key) {
            $this->envBackup[$key] = $_ENV[$key] ?? null;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->envBackup as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
        Config::clearCache();
        $pdo = new ReflectionProperty(Database::class, 'pdo');
        $pdo->setValue(null, null);
        parent::tearDown();
    }

    public function testValidateBootPassesWhenConfigured(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['SESSION_SECURE'] = 'true';
        $_ENV['APP_URL'] = 'https://example.com';
        $_ENV['APP_KEY'] = str_repeat('a', 32);
        $_ENV['HEALTH_TOKEN'] = 'token';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_DATABASE'] = 'db';
        $_ENV['DB_USERNAME'] = 'u';
        $_ENV['DB_PASSWORD'] = 'secret';
        $_ENV['PRIVACY_DPO_EMAIL'] = 'dpo@test.com';
        $_ENV['SECURITY_CONTACT_EMAIL'] = 'sec@test.com';

        ProductionGuard::validateBoot();
        $this->addToAssertionCount(1);
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
        $_ENV['APP_KEY'] = 'key';
        $_ENV['HEALTH_TOKEN'] = 'health';
        $_ENV['SESSION_SECURE'] = 'true';

        $this->expectException(RuntimeException::class);
        ProductionGuard::validateBoot();
    }
}
