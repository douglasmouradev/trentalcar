<?php

declare(strict_types=1);

/**
 * Router para o servidor embutido do PHP (php -S … -t public public/router.php).
 * Serve ficheiros estáticos directamente; encaminha o resto para index.php.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$base = realpath(__DIR__) ?: __DIR__;
$candidate = realpath($base . $uri);

if ($uri !== '/' && $candidate !== false && str_starts_with($candidate, $base . DIRECTORY_SEPARATOR) && is_file($candidate)) {
    return false;
}

require __DIR__ . '/index.php';
