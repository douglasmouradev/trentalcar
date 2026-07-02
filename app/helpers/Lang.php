<?php

declare(strict_types=1);

final class Lang
{
    /** @var array<string, string>|null */
    private static ?array $strings = null;

    public static function locale(): string
    {
        if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['pt-BR', 'en-US'], true)) {
            return $_SESSION['lang'];
        }
        if (!empty($_SESSION['user']['lang_pref'])) {
            return $_SESSION['user']['lang_pref'];
        }
        $app = Config::app();
        return $app['default_lang'];
    }

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, ['pt-BR', 'en-US'], true)) {
            $_SESSION['lang'] = $locale;
        }
    }

    /** @return array<string, string> */
    public static function load(): array
    {
        if (self::$strings !== null) {
            return self::$strings;
        }
        $file = BASE_PATH . '/lang/' . self::locale() . '.php';
        self::$strings = is_readable($file) ? (require $file) : [];
        return self::$strings;
    }

    /** @param array<string, string|int|float> $replace */
    public static function get(string $key, array $replace = []): string
    {
        $all = self::load();
        $text = $all[$key] ?? $key;
        $keys = array_keys($replace);
        usort($keys, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        foreach ($keys as $k) {
            $text = str_replace(':' . $k, (string) $replace[$k], $text);
        }
        return $text;
    }

    /** @param array<string, string|int|float> $replace */
    public static function e(string $key, array $replace = []): string
    {
        return htmlspecialchars(self::get($key, $replace), ENT_QUOTES, 'UTF-8');
    }
}
