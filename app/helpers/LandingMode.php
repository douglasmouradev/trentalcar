<?php

declare(strict_types=1);

final class LandingMode
{
    public static function isEnabled(): bool
    {
        $raw = $_ENV['APP_LANDING'] ?? Config::app()['landing_enabled'] ?? 'true';
        $env = strtolower(trim((string) $raw));

        return !in_array($env, ['0', 'false', 'no', 'off'], true);
    }
}
