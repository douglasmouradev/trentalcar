<?php

declare(strict_types=1);

final class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    /** Flash de sucesso com chave i18n e placeholders. */
    public static function successKey(string $key, array $replace = []): void
    {
        self::success(Lang::get($key, $replace));
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /** @return array<string, array<int, string>> */
    public static function pull(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return is_array($f) ? $f : [];
    }
}
