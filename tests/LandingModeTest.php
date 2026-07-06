<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LandingModeTest extends TestCase
{
    /** @var array<string, string|null> */
    private array $envBackup = [];

    protected function setUp(): void
    {
        $this->envBackup['APP_LANDING'] = $_ENV['APP_LANDING'] ?? null;
        Config::clearCache();
    }

    protected function tearDown(): void
    {
        if ($this->envBackup['APP_LANDING'] === null) {
            unset($_ENV['APP_LANDING']);
        } else {
            $_ENV['APP_LANDING'] = $this->envBackup['APP_LANDING'];
        }
        Config::clearCache();
        parent::tearDown();
    }

    public function testEnabledByDefault(): void
    {
        unset($_ENV['APP_LANDING']);
        Config::clearCache();
        self::assertTrue(LandingMode::isEnabled());
    }

    public function testDisabledWithOffValues(): void
    {
        foreach (['0', 'false', 'off', 'no'] as $value) {
            $_ENV['APP_LANDING'] = $value;
            Config::clearCache();
            self::assertFalse(LandingMode::isEnabled(), 'Expected off for ' . $value);
        }
    }
}
