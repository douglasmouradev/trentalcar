<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

require BASE_PATH . '/app/helpers/Env.php';
Env::load(BASE_PATH . '/.env');
Env::hydrateFromGetenv([
    'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'APP_URL', 'APP_ENV', 'APP_KEY', 'SMOKE_BASE_URL',
]);

ProductionGuard::validateBootOrRespond();

// Carrega configuração da app depois de Env::load e autoloader disponíveis
$appCfg = Config::app();

$lifetime = (int) ($appCfg['session_lifetime'] ?? 480) * 60;
ini_set('session.gc_maxlifetime', (string) $lifetime);
$secure = (bool) ($appCfg['session_secure'] ?? false);
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

RequestId::get();

SecurityHeaders::send();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt-BR', 'en-US'], true)) {
    Lang::setLocale($_GET['lang']);
    $back = Auth::check()
        ? Router::url(Auth::isPartner() ? '/partner/profile' : '/dashboard')
        : Router::url('/');
    $back = SafeRedirect::sameOriginOr($back, $_SERVER['HTTP_REFERER'] ?? null, $appCfg['url'] ?? '');
    header('Location: ' . $back);
    exit;
}

$routes = require BASE_PATH . '/config/routes.php';
try {
    Router::dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/', $routes);
} catch (Throwable $e) {
    AppError::log($e);
    AppError::render($e);
}
