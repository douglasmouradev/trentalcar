<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

spl_autoload_register(static function (string $class): void {
    foreach (['helpers', 'middleware', 'controllers', 'models', 'services'] as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/app/helpers/Env.php';
Env::load(BASE_PATH . '/.env');

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
