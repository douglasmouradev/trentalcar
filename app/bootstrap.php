<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}

if (!defined('TITANIUM_AUTOLOAD_REGISTERED')) {
    define('TITANIUM_AUTOLOAD_REGISTERED', true);
    spl_autoload_register(static function (string $class): void {
        foreach (['helpers', 'middleware', 'controllers', 'models', 'services'] as $dir) {
            $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    });
}
