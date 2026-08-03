<?php declare(strict_types=1);
/** Favicon e ícones para separadores / PWA (logo Titanium). */
$icon = htmlspecialchars(Router::url('/assets/img/logo.png'), ENT_QUOTES, 'UTF-8');
$svg = htmlspecialchars(Router::url('/assets/favicon.svg'), ENT_QUOTES, 'UTF-8');
$ico = htmlspecialchars(Router::url('/favicon.ico'), ENT_QUOTES, 'UTF-8');
?>
<link rel="icon" href="<?= $svg ?>" type="image/svg+xml">
<link rel="icon" href="<?= $ico ?>" sizes="32x32">
<link rel="icon" href="<?= $icon ?>" type="image/jpeg" sizes="192x192">
<link rel="apple-touch-icon" href="<?= $icon ?>">
