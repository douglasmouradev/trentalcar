<?php

declare(strict_types=1);

final class AuthMiddleware
{
    /** @var list<string> */
    private const PASSWORD_CHANGE_ALLOWED = [
        '/account/password',
        '/logout',
    ];

    public static function handle(): void
    {
        if (!Auth::ensureValidSession()) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (!Auth::mustChangePassword()) {
            return;
        }
        $path = self::currentPath();
        foreach (self::PASSWORD_CHANGE_ALLOWED as $allowed) {
            if ($path === $allowed || str_starts_with($path, $allowed . '/')) {
                return;
            }
        }
        Flash::error(Lang::get('auth.change_password_required'));
        header('Location: ' . Router::url('/account/password'));
        exit;
    }

    private static function currentPath(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = Router::basePath();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/') ?: '/';
        }
        return $uri;
    }
}
