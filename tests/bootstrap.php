<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/helpers/Env.php';
if (is_file(BASE_PATH . '/.env')) {
    Env::load(BASE_PATH . '/.env');
}

spl_autoload_register(static function (string $class): void {
    foreach (['helpers', 'middleware', 'controllers', 'models', 'services'] as $dir) {
        $file = BASE_PATH . '/app/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
