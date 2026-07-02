<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
define('TITANIUM_TESTING', true);

require_once BASE_PATH . '/vendor/autoload.php';

if (is_file(BASE_PATH . '/.env')) {
    Env::load(BASE_PATH . '/.env');
}

foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_ENV', 'APP_KEY', 'SMOKE_BASE_URL'] as $var) {
    $fromEnv = getenv($var);
    if ($fromEnv !== false && (!isset($_ENV[$var]) || $_ENV[$var] === '')) {
        $_ENV[$var] = $fromEnv;
    }
}

require_once BASE_PATH . '/tests/HttpTestClient.php';
