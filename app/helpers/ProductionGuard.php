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
        foreach (['APP_URL', 'APP_KEY', 'HEALTH_TOKEN', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $name) {
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

        if (!filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $missing[] = 'SESSION_SECURE (deve ser true com HTTPS)';
        }

        if ($missing !== []) {
            throw new RuntimeException('Produção mal configurada: ' . implode(', ', $missing));
        }
    }

    /** Responde 503 em HTTP quando a configuração de produção é inválida. */
    public static function validateBootOrRespond(): void
    {
        try {
            self::validateBoot();
        } catch (RuntimeException $e) {
            AppError::log($e);
            if (PHP_SAPI !== 'cli' && !headers_sent()) {
                http_response_code(503);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Indisponível</title></head><body><p>Configuração inválida.</p></body></html>';
            }
            exit(1);
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
