<?php declare(strict_types=1);
/** @var callable(string): string $asset */
/** @var string $locale */
/** @var string|null $activeNav home|booking|consult */
$activeNav = $activeNav ?? '';
$appRoot = rtrim(Router::url('/'), '/');
?>
<header class="site-header lp-header is-scrolled" id="lp-header">
    <a class="brand" href="<?= $asset('/') ?>">
        <img src="<?= $asset('/assets/img/logo.png') ?>" width="44" height="44" alt="<?= Lang::e('app.name') ?>">
        <span class="brand-text">
            <strong>Titanium</strong>
            <span><?= Lang::e('landing.brand_sub') ?></span>
        </span>
    </a>
    <div class="lp-header-right">
        <div class="lang-switch" id="langSwitch">
            <a href="#" class="lang-btn <?= $locale === 'pt-BR' ? 'active' : '' ?>" data-lang="pt-BR" title="<?= Lang::e('lang.pt') ?>">PT</a>
            <a href="#" class="lang-btn <?= $locale === 'en-US' ? 'active' : '' ?>" data-lang="en-US" title="<?= Lang::e('lang.en') ?>">EN</a>
        </div>
        <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="site-nav" aria-label="<?= Lang::e('landing.nav_toggle') ?>">
            <span></span>
        </button>
        <nav class="site-nav" id="site-nav" data-site-nav aria-label="<?= Lang::e('landing.nav_main') ?>">
            <a href="<?= $asset('/') ?>"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>><?= Lang::e('booking.back_home') ?></a>
            <a href="<?= $asset('/reservar') ?>"<?= $activeNav === 'booking' ? ' aria-current="page"' : '' ?>><?= Lang::e('booking.title') ?></a>
            <a href="<?= $asset('/consultar') ?>"<?= $activeNav === 'consult' ? ' aria-current="page"' : '' ?>><?= Lang::e('consult.title') ?></a>
            <a data-href-app="/login"><?= Lang::e('landing.nav_conta') ?></a>
        </nav>
    </div>
</header>
