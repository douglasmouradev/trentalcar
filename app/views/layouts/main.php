<?php
declare(strict_types=1);
/** @var string $content */
/** @var string $title */
$flash = Flash::pull();
$isOwner = Auth::isOwner();
$isPartner = Auth::isPartner();
$logged = Auth::check();
$locale = Lang::locale();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(str_replace('_', '-', $locale), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a3a6c">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Serif:wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(Router::url('/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="theme-titanium">
<a class="skip-link" href="#main-content"><?= Lang::e('a11y.skip_content') ?></a>
<div class="app-shell" id="appShell">
    <?php if ($logged): ?>
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= htmlspecialchars(Router::url('/assets/img/logo.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= Lang::e('app.name') ?>" class="brand-logo" width="40" height="40">
            <div>
                <div class="brand-title"><?= Lang::e('app.name') ?></div>
                <div class="brand-sub"><?= Lang::e('app.tagline') ?></div>
            </div>
        </div>
        <nav class="nav">
            <a class="nav-link" href="<?= Router::url('/dashboard') ?>"><?= Lang::e('nav.dashboard') ?></a>
            <a class="nav-link" href="<?= Router::url('/cars') ?>"><?= Lang::e('nav.cars') ?></a>
            <?php if (!$isPartner): ?>
            <a class="nav-link" href="<?= Router::url('/reservations') ?>"><?= Lang::e('nav.reservations') ?></a>
            <a class="nav-link" href="<?= Router::url('/reservations/calendar') ?>"><?= Lang::e('nav.calendar') ?></a>
            <a class="nav-link" href="<?= Router::url('/customers') ?>"><?= Lang::e('nav.customers') ?></a>
            <?php endif; ?>
            <?php if ($isOwner): ?>
                <a class="nav-link" href="<?= Router::url('/locations') ?>"><?= Lang::e('nav.locations') ?></a>
                <a class="nav-link" href="<?= Router::url('/users') ?>"><?= Lang::e('nav.users') ?></a>
                <a class="nav-link" href="<?= Router::url('/reports') ?>"><?= Lang::e('nav.reports') ?></a>
                <a class="nav-link" href="<?= Router::url('/leads') ?>"><?= Lang::e('nav.leads') ?></a>
                <a class="nav-link" href="<?= Router::url('/audit') ?>"><?= Lang::e('nav.audit') ?></a>
            <?php endif; ?>
        </nav>
    </aside>
    <?php endif; ?>
    <div class="main-wrap <?= $logged ? '' : 'main-wrap-full' ?>">
        <?php if ($logged): ?>
        <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" aria-label="<?= Lang::e('a11y.close_menu') ?>" tabindex="-1"></button>
        <?php endif; ?>
        <header class="topbar">
            <?php if ($logged): ?>
            <button type="button" class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="<?= Lang::e('a11y.open_menu') ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <?php endif; ?>
            <div class="topbar-spacer"></div>
            <div class="lang-switch" id="langSwitch">
                <a href="#" class="lang-btn <?= $locale === 'pt-BR' ? 'active' : '' ?>" data-lang="pt-BR" title="<?= Lang::e('lang.pt') ?>">PT</a>
                <a href="#" class="lang-btn <?= $locale === 'en-US' ? 'active' : '' ?>" data-lang="en-US" title="<?= Lang::e('lang.en') ?>">EN</a>
            </div>
            <?php if ($logged): ?>
            <div class="user-menu">
                <span class="user-name"><?= htmlspecialchars((string) (Auth::user()['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <form method="post" action="<?= Router::url('/logout') ?>" class="inline-form">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-ghost btn-sm"><?= Lang::e('nav.logout') ?></button>
                </form>
            </div>
            <?php endif; ?>
        </header>
        <main class="content" id="main-content" tabindex="-1">
            <?php foreach ($flash as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="toast toast-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
        <footer class="footer">
            <div class="footer-row">
                <span><?= Lang::e('app.name') ?> · <?= Lang::e('footer.rights') ?></span>
                <nav class="footer-legal" aria-label="<?= Lang::e('legal.footer_nav_label') ?>">
                    <a href="<?= htmlspecialchars(Router::url('/privacidade'), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('legal.nav_privacy') ?></a>
                    <a href="<?= htmlspecialchars(Router::url('/termos'), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('legal.nav_terms') ?></a>
                </nav>
            </div>
        </footer>
    </div>
</div>
<?php include APP_PATH . '/views/partials/cookie_notice.php'; ?>
<script<?= CspNonce::attr() ?>>window.APP_BASE_URL = <?= json_encode(rtrim(Router::url('/'), '/'), JSON_THROW_ON_ERROR) ?>;</script>
<script src="<?= htmlspecialchars(Router::url('/js/lang-switcher.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Router::url('/js/cookie-notice.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Router::url('/js/app.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
