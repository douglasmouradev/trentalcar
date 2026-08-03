<?php
declare(strict_types=1);
/** @var string $content */
/** @var string $title */
$flash = Flash::pull();
$isOwner = Auth::isOwner();
$isPartner = Auth::isPartner();
$logged = Auth::check();
$locale = Lang::locale();
$userName = $logged ? (string) (Auth::user()['name'] ?? '') : '';
$userInitials = '';
if ($userName !== '') {
    $parts = preg_split('/\s+/u', trim($userName)) ?: [];
    foreach (array_slice($parts, 0, 2) as $part) {
        $userInitials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(str_replace('_', '-', $locale), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a3a6c">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="app-base-url" content="<?= htmlspecialchars(rtrim(Router::url('/'), '/'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <?php View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="theme-titanium" data-usd-brl-rate="<?= htmlspecialchars((string) ExchangeRate::rate(), ENT_QUOTES, 'UTF-8') ?>">
<a class="skip-link" href="#main-content"><?= Lang::e('a11y.skip_content') ?></a>
<div class="app-shell<?= $logged ? ' app-shell--nav-drawer' : '' ?>" id="appShell">
    <?php if ($logged): ?>
    <aside class="sidebar" id="sidebar" aria-hidden="true">
        <div class="brand">
            <img src="<?= htmlspecialchars(Router::url('/assets/img/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= Lang::e('app.name') ?>" class="brand-logo" width="72" height="72">
            <div>
                <div class="brand-title"><?= Lang::e('app.name') ?></div>
                <div class="brand-sub"><?= Lang::e('app.tagline') ?></div>
            </div>
        </div>
        <nav class="nav">
            <?php if ($isPartner): ?>
            <a class="nav-link" href="<?= Router::url('/partner/profile') ?>"><?= Lang::e('partner.my_profile') ?></a>
            <a class="nav-link" href="<?= Router::url('/cars') ?>"><?= Lang::e('nav.cars') ?></a>
            <?php else: ?>
            <a class="nav-link" href="<?= Router::url('/dashboard') ?>"><?= Lang::e('nav.dashboard') ?></a>
            <a class="nav-link" href="<?= Router::url('/cars') ?>"><?= Lang::e('nav.cars') ?></a>
            <a class="nav-link" href="<?= Router::url('/reservations') ?>"><?= Lang::e('nav.reservations') ?></a>
            <a class="nav-link" href="<?= Router::url('/reservations/calendar') ?>"><?= Lang::e('nav.calendar') ?></a>
            <a class="nav-link" href="<?= Router::url('/customers') ?>"><?= Lang::e('nav.customers') ?></a>
            <?php if (Auth::isStaff()): ?>
                <a class="nav-link" href="<?= Router::url('/leads') ?>"><?= Lang::e('nav.leads') ?><?php $leadCount = Lead::countNewCached(); if ($leadCount > 0): ?><span class="nav-badge" aria-label="<?= htmlspecialchars(Lang::get('leads.new_count', ['count' => $leadCount]), ENT_QUOTES, 'UTF-8') ?>"><?= $leadCount > 99 ? '99+' : $leadCount ?></span><?php endif; ?></a>
            <?php endif; ?>
            <?php if ($isOwner): ?>
                <a class="nav-link" href="<?= Router::url('/locations') ?>"><?= Lang::e('nav.locations') ?></a>
                <a class="nav-link" href="<?= Router::url('/partners') ?>"><?= Lang::e('nav.partners') ?></a>
                <a class="nav-link" href="<?= Router::url('/users') ?>"><?= Lang::e('nav.users') ?></a>
                <a class="nav-link" href="<?= Router::url('/reports') ?>"><?= Lang::e('nav.reports') ?></a>
                <a class="nav-link" href="<?= Router::url('/audit') ?>"><?= Lang::e('nav.audit') ?></a>
            <?php endif; ?>
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
            <button type="button" class="icon-btn sidebar-toggle" id="sidebarToggle"
                    aria-label="<?= Lang::e('a11y.open_menu') ?>"
                    aria-expanded="false"
                    aria-controls="sidebar"
                    title="<?= Lang::e('a11y.open_menu') ?>"
                    data-label-open="<?= htmlspecialchars(Lang::get('a11y.open_menu'), ENT_QUOTES, 'UTF-8') ?>"
                    data-label-close="<?= htmlspecialchars(Lang::get('a11y.close_menu'), ENT_QUOTES, 'UTF-8') ?>">
                <span class="hamburger" aria-hidden="true">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </span>
            </button>
            <div class="topbar-brand">
                <img src="<?= htmlspecialchars(Router::url('/assets/img/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="" class="topbar-brand-logo" width="52" height="52">
                <span class="topbar-brand-name"><?= Lang::e('app.name') ?></span>
            </div>
            <?php endif; ?>
            <div class="topbar-spacer"></div>
            <?php if ($logged && !$isPartner): ?>
            <button type="button" class="icon-btn search-toggle-btn" id="searchToggle" aria-label="<?= Lang::e('a11y.search') ?>" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
            <div class="global-search-wrap" id="globalSearchWrap">
                <input type="search" id="globalSearch" class="input global-search-input" placeholder="<?= Lang::e('search.placeholder') ?>" autocomplete="off" aria-label="<?= Lang::e('search.placeholder') ?>">
                <div id="globalSearchResults" class="global-search-results" hidden></div>
            </div>
            <?php endif; ?>
            <div class="lang-switch" id="langSwitch">
                <a href="#" class="lang-btn <?= $locale === 'pt-BR' ? 'active' : '' ?>" data-lang="pt-BR" title="<?= Lang::e('lang.pt') ?>">PT</a>
                <a href="#" class="lang-btn <?= $locale === 'en-US' ? 'active' : '' ?>" data-lang="en-US" title="<?= Lang::e('lang.en') ?>">EN</a>
            </div>
            <?php if ($logged): ?>
            <div class="user-menu" id="userMenu">
                <button type="button" class="icon-btn user-menu-toggle" id="userMenuToggle" aria-expanded="false" aria-controls="userMenuPanel" aria-label="<?= Lang::e('a11y.user_menu') ?>">
                    <span class="user-avatar" aria-hidden="true"><?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8') ?></span>
                </button>
                <div class="user-menu-panel" id="userMenuPanel" hidden>
                    <span class="user-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                    <a class="btn btn-ghost btn-sm" href="<?= Router::url('/account/security') ?>"><?= Lang::e('account.security_title') ?></a>
                    <form method="post" action="<?= Router::url('/logout') ?>" class="inline-form">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm"><?= Lang::e('nav.logout') ?></button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </header>
        <main class="content" id="main-content" tabindex="-1">
            <?php foreach ($flash as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isError = $type === 'error'; ?>
                    <div class="toast toast-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" role="<?= $isError ? 'alert' : 'status' ?>" aria-live="<?= $isError ? 'assertive' : 'polite' ?>">
                        <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
                        <button type="button" class="toast-dismiss" aria-label="<?= Lang::e('actions.close') ?>">&times;</button>
                    </div>
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
<script src="<?= htmlspecialchars(Asset::url('/js/lang-switcher.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/js/cookie-notice.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/js/app.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php if ($logged): ?>
<script>window.__i18n={searchNoResults:<?= json_encode(Lang::get('search.no_results'), JSON_THROW_ON_ERROR) ?>,searchLoading:<?= json_encode(Lang::get('search.loading'), JSON_THROW_ON_ERROR) ?>,searchError:<?= json_encode(Lang::get('search.error'), JSON_THROW_ON_ERROR) ?>};</script>
<script src="<?= htmlspecialchars(Router::url('/js/global-search.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Router::url('/js/form-masks.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php endif; ?>
</body>
</html>
