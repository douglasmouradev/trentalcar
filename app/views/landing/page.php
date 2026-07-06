<?php

declare(strict_types=1);

/** @var string $title */
/** @var string|null $lead_banner ok|limite|erro|null */
/** @var array<int,array<string,mixed>> $fleetCars */
$asset = static function (string $path): string {
    return htmlspecialchars(Router::url($path), ENT_QUOTES, 'UTF-8');
};
$appRoot = rtrim(Router::url('/'), '/');
$locale = Lang::locale();
$htmlLang = str_replace('_', '-', $locale);
$metaDesc = Lang::get('landing.meta_description');
$ogLocale = $locale === 'en-US' ? 'en_US' : 'pt_BR';
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>" data-app-origin="<?= htmlspecialchars($appRoot, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php
  $canonical = Router::url('/');
  $ogImage = Router::url('/assets/img/logo.jpeg');
  ?>
  <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="theme-color" content="#152238">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="alternate" hreflang="pt-BR" href="<?= htmlspecialchars(Router::url('/?lang=pt-BR'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="alternate" hreflang="en-US" href="<?= htmlspecialchars(Router::url('/?lang=en-US'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:locale" content="<?= htmlspecialchars($ogLocale, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <?php View::partial('partials/favicon'); ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/landing/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
  <script type="application/ld+json"><?= json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'AutoRental',
      'name' => Lang::get('app.name'),
      'url' => $canonical,
      'telephone' => Contact::phoneDisplay(),
      'email' => Contact::email(),
      'areaServed' => 'BR',
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="lp-body">
  <a class="skip-link" href="#conteudo"><?= Lang::e('a11y.skip_content') ?></a>

  <header class="site-header lp-header" id="lp-header">
    <a class="brand" href="#topo" id="topo">
      <img src="<?= $asset('/assets/img/logo.jpeg') ?>" width="44" height="44" alt="<?= Lang::e('app.name') ?>">
      <span class="brand-text">
        <span class="brand-name">Titanium Rental Car</span>
        <span class="brand-sub"><?= Lang::e('landing.brand_sub') ?></span>
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
        <a href="#frota"><?= Lang::e('landing.nav_frota') ?></a>
        <a href="<?= $asset('/reservar') ?>"><?= Lang::e('booking.title') ?></a>
        <a href="<?= $asset('/consultar') ?>"><?= Lang::e('consult.title') ?></a>
        <a href="#vantagens"><?= Lang::e('landing.nav_vantagens') ?></a>
        <a href="#como-funciona"><?= Lang::e('landing.nav_como') ?></a>
        <a href="#faq"><?= Lang::e('landing.nav_faq') ?></a>
        <a class="btn btn-ghost" data-href-app="/login"><?= Lang::e('landing.nav_conta') ?></a>
        <a class="btn btn-primary" href="#reserva"><?= Lang::e('landing.nav_reservar') ?></a>
      </nav>
    </div>
  </header>

  <div class="lp-notice-bar" role="status">
    <p><?= Lang::e('landing.notice_bar', [
        'rate' => Contact::minDailyRate(),
        'hours' => Contact::businessHours(),
        'response' => Contact::responseTime(),
    ]) ?></p>
  </div>

  <main id="conteudo">
    <?php if (($lead_banner ?? null) === 'ok'): ?>
      <p class="lp-lead-banner lp-lead-banner--ok" role="status"><?= Lang::e('landing.lead_ok', ['response' => Contact::responseTime()]) ?>
        <?php if (!empty($leadWhatsappUrl)): ?>
          <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars((string) $leadWhatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.lead_whatsapp') ?></a>
        <?php endif; ?>
      </p>
    <?php elseif (($lead_banner ?? null) === 'limite'): ?>
      <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= Lang::e('landing.lead_limite', ['phone' => Contact::phoneDisplay()]) ?></p>
    <?php elseif (($lead_banner ?? null) === 'erro'): ?>
      <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= Lang::e('landing.lead_erro') ?></p>
    <?php endif; ?>
    <section class="lp-hero" aria-labelledby="lp-hero-title">
      <div class="lp-hero-stage">
        <a class="lp-hero-brand" href="#topo" aria-label="<?= Lang::e('app.name') ?>">
          <img class="lp-hero-brand-logo" src="<?= $asset('/assets/img/logo.jpeg') ?>" alt="<?= Lang::e('app.name') ?>" width="280" height="120" decoding="async" fetchpriority="high">
        </a>
        <div class="lp-hero-inner">
          <p class="lp-hero-kicker"><?= Lang::e('landing.hero_kicker') ?></p>
          <h1 id="lp-hero-title"><?= Lang::e('landing.hero_title') ?></h1>
          <p class="lp-hero-lead"><?= Lang::e('landing.hero_lead') ?></p>
          <div class="lp-hero-actions">
            <a class="btn btn-primary btn-lg" href="#reserva"><?= Lang::e('landing.nav_reservar') ?></a>
            <a class="btn btn-hero-ghost btn-lg" href="#frota"><?= Lang::e('landing.nav_frota') ?></a>
          </div>
        </div>
      </div>
      <div class="lp-booking-anchor" id="reserva"></div>
      <div class="lp-booking-wrap">
        <?php
        View::partial('partials/landing_lead_form', [
            'asset' => $asset,
            'leadOld' => $leadOld ?? [],
            'leadErrors' => $leadErrors ?? [],
            'selectedCar' => $selectedCar ?? null,
            'formAction' => '/lead',
            'returnPath' => '/',
        ]);
        ?>
      </div>
    </section>

    <section class="lp-trust" aria-label="<?= Lang::e('landing.trust_aria') ?>">
      <div class="lp-trust-inner">
        <div class="lp-trust-item">
          <span class="lp-trust-ic" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
          </span>
          <strong><?= Lang::e('landing.trust_1_title') ?></strong>
          <span><?= Lang::e('landing.trust_1_desc') ?></span>
        </div>
        <div class="lp-trust-item">
          <span class="lp-trust-ic" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </span>
          <strong><?= Lang::e('landing.trust_2_title') ?></strong>
          <span><?= Lang::e('landing.trust_2_desc') ?></span>
        </div>
        <div class="lp-trust-item">
          <span class="lp-trust-ic" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </span>
          <strong><?= Lang::e('landing.trust_3_title') ?></strong>
          <span><?= Lang::e('landing.trust_3_desc') ?></span>
        </div>
        <div class="lp-trust-item">
          <span class="lp-trust-ic" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </span>
          <strong><?= Lang::e('landing.trust_4_title') ?></strong>
          <span><?= Lang::e('landing.trust_4_desc') ?></span>
        </div>
      </div>
    </section>

    <section class="lp-promo" data-reveal>
      <div class="lp-promo-inner">
        <p class="lp-promo-tag"><?= Lang::e('landing.promo_tag') ?></p>
        <p class="lp-promo-text"><strong><?= Lang::e('landing.promo_lead') ?></strong><?= Lang::e('landing.promo_tail') ?></p>
        <a class="lp-promo-link" href="#frota"><?= Lang::e('landing.promo_link') ?></a>
      </div>
    </section>

    <section class="lp-section lp-section--wide" id="frota" data-reveal>
      <header class="lp-section-head">
        <span class="lp-section-eyebrow"><?= Lang::e('landing.nav_frota') ?></span>
        <h2><?= Lang::e('landing.fleet_title') ?></h2>
        <p><?= Lang::e('landing.fleet_lead') ?></p>
      </header>
      <div class="lp-fleet-toolbar">
        <p class="lp-fleet-hint"><?= Lang::e('landing.fleet_filter_hint') ?></p>
        <div class="lp-filters" role="group" aria-label="<?= Lang::e('landing.fleet_filter_group') ?>">
          <button type="button" class="lp-filter is-active" data-filter="all" aria-pressed="true"><?= Lang::e('landing.filter_all') ?></button>
          <button type="button" class="lp-filter" data-filter="economy" aria-pressed="false"><?= Lang::e('landing.filter_economy') ?></button>
          <button type="button" class="lp-filter" data-filter="compact" aria-pressed="false"><?= Lang::e('landing.filter_compact') ?></button>
          <button type="button" class="lp-filter" data-filter="sedan" aria-pressed="false"><?= Lang::e('landing.filter_sedan') ?></button>
          <button type="button" class="lp-filter" data-filter="suv" aria-pressed="false"><?= Lang::e('landing.filter_suv') ?></button>
          <button type="button" class="lp-filter" data-filter="exec" aria-pressed="false"><?= Lang::e('landing.filter_exec') ?></button>
          <button type="button" class="lp-filter" data-filter="util" aria-pressed="false"><?= Lang::e('landing.filter_util') ?></button>
        </div>
      </div>
      <div class="lp-fleet" id="lp-fleet-grid">
        <?php include APP_PATH . '/views/partials/landing_fleet.php'; ?>
      </div>
      <p class="lp-fleet-empty muted" id="lp-fleet-filter-empty" hidden><?= Lang::e('landing.fleet_filter_empty') ?></p>
    </section>

    <section class="lp-section lp-section--muted" id="vantagens" data-reveal>
      <div class="lp-section--wide lp-split">
        <header class="lp-section-head">
          <span class="lp-section-eyebrow"><?= Lang::e('landing.nav_vantagens') ?></span>
          <h2><?= Lang::e('landing.adv_title') ?></h2>
          <p><?= Lang::e('landing.adv_lead') ?></p>
        </header>
        <ul class="lp-benefits">
          <li><strong><?= Lang::e('landing.adv_1_title') ?></strong> — <?= Lang::e('landing.adv_1_desc') ?></li>
          <li><strong><?= Lang::e('landing.adv_2_title') ?></strong> — <?= Lang::e('landing.adv_2_desc') ?></li>
          <li><strong><?= Lang::e('landing.adv_3_title') ?></strong> — <?= Lang::e('landing.adv_3_desc') ?></li>
          <li><strong><?= Lang::e('landing.adv_4_title') ?></strong> — <?= Lang::e('landing.adv_4_desc') ?></li>
        </ul>
      </div>
    </section>

    <section class="lp-section lp-section--wide" id="como-funciona" data-reveal>
      <header class="lp-section-head">
        <span class="lp-section-eyebrow"><?= Lang::e('landing.nav_como') ?></span>
        <h2><?= Lang::e('landing.steps_title') ?></h2>
        <p><?= Lang::e('landing.steps_lead') ?></p>
      </header>
      <ol class="lp-steps">
        <li>
          <span class="lp-step-num">1</span>
          <div>
            <h3><?= Lang::e('landing.step_1_title') ?></h3>
            <p><?= Lang::e('landing.step_1_desc') ?></p>
          </div>
        </li>
        <li>
          <span class="lp-step-num">2</span>
          <div>
            <h3><?= Lang::e('landing.step_2_title') ?></h3>
            <p><?= Lang::e('landing.step_2_desc') ?></p>
          </div>
        </li>
        <li>
          <span class="lp-step-num">3</span>
          <div>
            <h3><?= Lang::e('landing.step_3_title') ?></h3>
            <p><?= Lang::e('landing.step_3_desc') ?></p>
          </div>
        </li>
      </ol>
    </section>

    <section class="lp-section lp-section--wide" id="faq" data-reveal>
      <header class="lp-section-head">
        <span class="lp-section-eyebrow">FAQ</span>
        <h2><?= Lang::e('landing.faq_title') ?></h2>
      </header>
      <div class="lp-faq">
        <details class="lp-faq-item">
          <summary><?= Lang::e('landing.faq_1_q') ?></summary>
          <p><?= Lang::e('landing.faq_1_a') ?></p>
        </details>
        <details class="lp-faq-item">
          <summary><?= Lang::e('landing.faq_2_q') ?></summary>
          <p><?= Lang::e('landing.faq_2_a') ?></p>
        </details>
        <details class="lp-faq-item">
          <summary><?= Lang::e('landing.faq_3_q') ?></summary>
          <p><?= Lang::e('landing.faq_3_a') ?></p>
        </details>
      </div>
    </section>

    <section class="lp-cta" id="contato" data-reveal>
      <div class="lp-cta-inner">
        <div>
          <h2><?= Lang::e('landing.cta_title') ?></h2>
          <p><?= Lang::e('landing.cta_lead', ['hours' => Contact::businessHours()]) ?></p>
        </div>
        <div class="lp-cta-actions">
          <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars(Contact::whatsappUrl(), ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer" target="_blank"><?= Lang::e('landing.cta_wa') ?></a>
          <a class="btn btn-ghost btn-lg" href="tel:<?= htmlspecialchars(Contact::phoneTel(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(Contact::phoneDisplay(), ENT_QUOTES, 'UTF-8') ?></a>
          <a class="btn btn-ghost btn-lg" href="mailto:<?= htmlspecialchars(Contact::email(), ENT_QUOTES, 'UTF-8') ?>?subject=Reserva%20-%20Titanium"><?= htmlspecialchars(Contact::email(), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </div>
    </section>
  </main>

  <?php View::partial('partials/landing_footer', ['asset' => $asset, 'compact' => false]); ?>

  <?php View::partial('partials/cookie_notice'); ?>
  <script src="<?= $asset('/js/lang-switcher.js') ?>" defer></script>
  <script src="<?= $asset('/js/cookie-notice.js') ?>" defer></script>
  <script src="<?= $asset('/landing/js/site.js') ?>" defer></script>
  <script src="<?= $asset('/landing/js/lead-form.js') ?>" defer></script>
</body>
</html>
