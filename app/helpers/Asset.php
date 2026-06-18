<?php

declare(strict_types=1);

final class Asset
{
    public static function url(string $path): string
    {
        $path = str_starts_with($path, '/') ? $path : '/' . $path;
        $full = BASE_PATH . '/public' . $path;
        $v = is_file($full) ? (string) filemtime($full) : '1';
        return Router::url($path) . '?v=' . rawurlencode($v);
    }
}
