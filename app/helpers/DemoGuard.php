<?php

declare(strict_types=1);

/** Bloqueia contas de demonstração em produção. */
final class DemoGuard
{
    /** @var list<string> */
    private const DEMO_EMAILS = [
        'owner@titaniumrental.com',
        'operator@titaniumrental.com',
        'partner@titaniumrental.com',
    ];

    public static function isDemoEmail(string $email): bool
    {
        return in_array(strtolower(trim($email)), self::DEMO_EMAILS, true);
    }

    public static function loginBlocked(string $email): bool
    {
        if (!ProductionGuard::isProduction()) {
            return false;
        }
        if (filter_var($_ENV['ALLOW_DEMO_LOGIN'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }
        return self::isDemoEmail($email);
    }
}
