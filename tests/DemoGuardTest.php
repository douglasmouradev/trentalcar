<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DemoGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['APP_ENV'], $_ENV['ALLOW_DEMO_LOGIN']);
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
