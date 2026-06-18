<?php

declare(strict_types=1);

final class SafeRedirect
{
    /** Redireciona para $referer apenas se host e path forem do mesmo site. */
    public static function sameOriginOr(string $fallback, ?string $referer, string $appUrl): string
    {
        if ($referer === null || $referer === '') {
            return $fallback;
        }

        $expectedHost = parse_url($appUrl, PHP_URL_HOST);
        $refHost = parse_url($referer, PHP_URL_HOST);
        $refScheme = parse_url($referer, PHP_URL_SCHEME);
        $refPath = parse_url($referer, PHP_URL_PATH) ?: '/';

        if (!is_string($expectedHost) || $expectedHost === '' || !is_string($refHost) || $refHost === '') {
            return $fallback;
        }

        if (!hash_equals(strtolower($expectedHost), strtolower($refHost))) {
            return $fallback;
        }

        $appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        if ($appScheme === 'https' && $refScheme !== 'https') {
            return $fallback;
        }

        if (!self::isSafeAppPath($refPath, $appUrl)) {
            return $fallback;
        }

        return $referer;
    }

    private static function isSafeAppPath(string $path, string $appUrl): bool
    {
        $base = parse_url($appUrl, PHP_URL_PATH) ?: '';
        $base = rtrim($base, '/');
        if ($base !== '' && $base !== '/') {
            if (!str_starts_with($path, $base . '/') && $path !== $base) {
                return false;
            }
            $path = substr($path, strlen($base)) ?: '/';
        }
        if ($path === '' || $path[0] !== '/') {
            return false;
        }
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            return false;
        }
        return true;
    }
}
