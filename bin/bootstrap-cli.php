<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$autoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    require BASE_PATH . '/app/bootstrap.php';
}

require BASE_PATH . '/app/helpers/Env.php';
Env::load(BASE_PATH . '/.env');

foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_ENV', 'APP_KEY'] as $var) {
    $fromEnv = getenv($var);
    if ($fromEnv !== false && (!isset($_ENV[$var]) || $_ENV[$var] === '')) {
        $_ENV[$var] = $fromEnv;
    }
}
