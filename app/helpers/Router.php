<?php

declare(strict_types=1);

final class Router
{
    /** Rotas autenticadas acessíveis a cotistas (parceiros). */
    private const PARTNER_ALLOWED_PREFIXES = [
        '/cars',
        '/partner/profile',
        '/account/',
        '/dashboard',
        '/logout',
        '/locale',
    ];

    /** @param array<string, array{0: string, 1: string, auth?: bool, role?: string}> $routes */
    public static function dispatch(string $method, string $uri, array $routes): void
    {
        $path = self::normalizePath($uri);

        foreach ($routes as $pattern => $def) {
            $parts = explode(':', $pattern, 2);
            $m = $parts[0] ?? '';
            $p = $parts[1] ?? '';
            if (strtoupper($m) !== strtoupper($method)) {
                continue;
            }
            $regex = self::patternToRegex($p);
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            $auth = $def['auth'] ?? false;
            $role = $def['role'] ?? null;
            if ($auth) {
                AuthMiddleware::handle();
                if (Auth::isPartner() && !self::partnerMayAccessPath($path)) {
                    PartnerForbiddenMiddleware::handle();
                }
            }
            if ($role !== null) {
                RoleMiddleware::handle($role);
            }
            $controller = $def[0];
            $action = $def[1];
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $class = $controller;
            if (!class_exists($class)) {
                self::fail500(new RuntimeException('Controller not found: ' . $class));
                return;
            }
            $c = new $class();
            if (!method_exists($c, $action)) {
                self::fail500(new RuntimeException('Action not found: ' . $class . '::' . $action));
                return;
            }
            $c->$action(...array_values($params));
            return;
        }
        http_response_code(404);
        View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
    }

    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/') ?: '/';
        }
        return $path;
    }

    public static function basePath(): string
    {
        $app = Config::app();
        return $app['base'] ?? '';
    }

    public static function url(string $path = ''): string
    {
        $app = Config::app();
        $base = $app['base'] ?? '';
        $path = $path === '' ? '/' : (str_starts_with($path, '/') ? $path : '/' . $path);
        return $app['url'] . $base . ($path === '/' ? '' : $path);
    }

    private static function partnerMayAccessPath(string $path): bool
    {
        foreach (self::PARTNER_ALLOWED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }
        return false;
    }

    private static function patternToRegex(string $pattern): string
    {
        $pattern = preg_replace('#\{id\}#', '(?P<id>\d+)', $pattern) ?? $pattern;
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern) ?? $pattern;
        return '#^' . $pattern . '$#';
    }

    private static function fail500(Throwable $e): void
    {
        AppError::log($e);
        AppError::render($e);
    }
}
