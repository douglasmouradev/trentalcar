<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DemoGuardTest extends TestCase
{
    /** @var array<string, string|null> */
    private array $envBackup = [];

    protected function setUp(): void
    {
        foreach (['APP_ENV', 'ALLOW_DEMO_LOGIN'] as $key) {
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
        parent::tearDown();
    }

    public function testDemoEmailDetected(): void
    {
        $this->assertTrue(DemoGuard::isDemoEmail('owner@titaniumrental.com'));
        $this->assertFalse(DemoGuard::isDemoEmail('real@empresa.com.br'));
    }

    public function testBlockedInProduction(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['ALLOW_DEMO_LOGIN'] = 'false';
        $this->assertTrue(DemoGuard::loginBlocked('operator@titaniumrental.com'));
    }

    public function testAllowedWhenFlagSet(): void
    {
        $_ENV['APP_ENV'] = 'production';
        $_ENV['ALLOW_DEMO_LOGIN'] = 'true';
        $this->assertFalse(DemoGuard::loginBlocked('owner@titaniumrental.com'));
    }
}
