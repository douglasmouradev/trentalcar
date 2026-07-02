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
