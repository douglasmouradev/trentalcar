<?php

declare(strict_types=1);

final class Env
{
    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }

    /** Preenche chaves vazias no $_ENV a partir de getenv() (CI, Docker, etc.). */
    /** @param list<string> $keys */
    public static function hydrateFromGetenv(array $keys): void
    {
        foreach ($keys as $var) {
            if (!is_string($var) || $var === '') {
                continue;
            }
            $fromEnv = getenv($var);
            if ($fromEnv === false) {
                continue;
            }
            if (!isset($_ENV[$var]) || trim((string) $_ENV[$var]) === '') {
                $_ENV[$var] = $fromEnv;
                putenv("{$var}={$fromEnv}");
            }
        }
    }
}
