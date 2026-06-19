<?php
declare(strict_types=1);
/** @var string $content */
/** @var string $title */
$locale = Lang::locale();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(str_replace('_', '-', $locale), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a3a6c">
    <meta name="app-base-url" content="<?= htmlspecialchars(rtrim(Router::url('/'), '/'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <?php View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="theme-titanium auth-page">
<a class="skip-link" href="#auth-main"><?= Lang::e('a11y.skip_content') ?></a>
<div class="auth-layout">
    <aside class="auth-aside" aria-label="<?= Lang::e('app.name') ?>">
        <div class="auth-aside-glow" aria-hidden="true"></div>
        <div class="auth-aside-grid" aria-hidden="true"></div>
        <div class="auth-aside-logo-wrap">
            <div class="auth-aside-logo-panel">
                <a class="auth-aside-brand" href="<?= htmlspecialchars(Router::url('/'), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= Lang::e('app.name') ?>">
                    <img class="auth-aside-logo" src="<?= htmlspecialchars(Router::url('/assets/img/logo.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= Lang::e('app.name') ?>" width="720" height="288" decoding="async" fetchpriority="high">
                </a>
            </div>
        </div>
        <div class="auth-aside-content">
            <p class="auth-aside-headline"><?= Lang::e('auth.aside_headline') ?></p>
            <p class="auth-aside-tagline"><?= Lang::e('app.tagline') ?></p>
        </div>
    </aside>
    <div class="auth-panel">
        <div class="auth-card" id="auth-main" tabindex="-1">
            <?php foreach (Flash::pull() as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="toast toast-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" aria-live="polite"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="auth-brand">
                <div class="auth-logo-frame">
                    <img src="<?= htmlspecialchars(Router::url('/assets/img/logo.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="" width="56" height="56" class="auth-logo" loading="lazy">
                </div>
                <p class="auth-eyebrow"><?= Lang::e('app.name') ?></p>
                <h1 class="auth-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <?= $content ?>
            <div class="auth-footer">
                <nav class="auth-legal-links" aria-label="<?= Lang::e('legal.footer_nav_label') ?>">
                    <a href="<?= htmlspecialchars(Router::url('/privacidade'), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('legal.nav_privacy') ?></a>
                    <span class="auth-legal-sep" aria-hidden="true">·</span>
                    <a href="<?= htmlspecialchars(Router::url('/termos'), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('legal.nav_terms') ?></a>
                </nav>
                <div class="lang-switch auth-lang" id="langSwitch">
                    <a href="#" class="lang-btn <?= $locale === 'pt-BR' ? 'active' : '' ?>" data-lang="pt-BR" title="<?= Lang::e('lang.pt') ?>">PT</a>
                    <a href="#" class="lang-btn <?= $locale === 'en-US' ? 'active' : '' ?>" data-lang="en-US" title="<?= Lang::e('lang.en') ?>">EN</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include APP_PATH . '/views/partials/cookie_notice.php'; ?>
<script src="<?= htmlspecialchars(Asset::url('/js/cookie-notice.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/js/lang-switcher.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/js/auth-ui.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
