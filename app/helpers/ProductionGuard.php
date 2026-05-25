<?php

declare(strict_types=1);

final class ProductionGuard
{
    public static function isProduction(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'production') === 'production';
    }

    /** Valida variáveis obrigatórias antes de servir tráfego. */
    public static function validateBoot(): void
    {
        if (!self::isProduction()) {
            return;
        }

        $missing = [];
        foreach (['APP_URL', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $name) {
            if (trim((string) ($_ENV[$name] ?? '')) === '') {
                $missing[] = $name;
            }
        }
        foreach (['PRIVACY_DPO_EMAIL', 'SECURITY_CONTACT_EMAIL'] as $name) {
            if (trim((string) ($_ENV[$name] ?? '')) === '') {
                $missing[] = $name;
            }
        }

        if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $missing[] = 'APP_DEBUG (deve ser false)';
        }

        if ($missing !== []) {
            throw new RuntimeException('Produção mal configurada: ' . implode(', ', $missing));
        }
    }

    public static function abortIfProduction(string $operation): void
    {
        if (self::isProduction()) {
            fwrite(STDERR, "Operação bloqueada em produção: {$operation}\n");
            exit(1);
        }
    }
}
