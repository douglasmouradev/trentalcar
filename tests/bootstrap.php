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

require_once BASE_PATH . '/tests/HttpTestClient.php';
