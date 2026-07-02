<?php

declare(strict_types=1);

/** Redirecionamento HTTP centralizado (substitui header+exit espalhados). */
final class Redirect
{
    public static function to(string $path): never
    {
        header('Location: ' . Router::url($path));
        exit;
    }

    public static function toUrl(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
