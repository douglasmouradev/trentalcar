<?php declare(strict_types=1);
/** @var array<string,mixed>|null $reservation */
/** @var string|null $error */
/** @var array{code:string,email:string} $old */
$asset = static fn (string $path): string => htmlspecialchars(Router::url($path), ENT_QUOTES, 'UTF-8');
$appRoot = rtrim(Router::url('/'), '/');
$locale = Lang::locale();
$htmlLang = str_replace('_', '-', $locale);
$old = $old ?? ['code' => '', 'email' => ''];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>" data-app-origin="<?= htmlspecialchars($appRoot, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#152238">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <?php View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/landing/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="lp-body lp-body--booking">
<a class="skip-link" href="#conteudo"><?= Lang::e('a11y.skip_content') ?></a>
<?php View::partial('partials/public_header', ['asset' => $asset, 'locale' => $locale, 'activeNav' => 'consult']); ?>

<main id="conteudo" class="lp-booking-page lp-consult-page">
    <section class="lp-section lp-section--wide">
        <header class="lp-section-head">
            <h1><?= Lang::e('consult.title') ?></h1>
            <p class="lp-section-lead"><?= Lang::e('consult.lead') ?></p>
        </header>
        <?php if (!empty($error)): ?>
            <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form class="lp-date-filter card" method="post" action="<?= $asset('/consultar') ?>" id="consult-form"
              data-label-submitting="<?= htmlspecialchars(Lang::get('consult.submitting'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::field() ?>
            <div class="lp-booking-grid lp-booking-grid--contact">
                <label class="lp-field">
                    <span class="lp-label"><?= Lang::e('reservation.code') ?></span>
                    <input class="lp-input mono" type="text" name="code" required maxlength="20" autocomplete="off"
                           value="<?= htmlspecialchars($old['code'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="lp-field">
                    <span class="lp-label"><?= Lang::e('auth.email') ?></span>
                    <input class="lp-input" type="email" name="email" required maxlength="180" autocomplete="email"
                           value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <div class="lp-field lp-field--btn">
                    <span class="lp-label lp-label--ghost" aria-hidden="true">&nbsp;</span>
                    <button class="btn btn-primary" type="submit"><?= Lang::e('consult.submit') ?></button>
                </div>
            </div>
        </form>
        <?php if ($reservation !== null): ?>
            <div class="lp-consult-result card">
                <h2 class="lp-consult-code mono"><?= htmlspecialchars((string) $reservation['code'], ENT_QUOTES, 'UTF-8') ?></h2>
                <dl class="lp-consult-dl">
                    <dt><?= Lang::e('reservation.status') ?></dt><dd><?= Ui::statusBadge((string) $reservation['status']) ?></dd>
                    <dt><?= Lang::e('reservation.customer') ?></dt><dd><?= htmlspecialchars((string) $reservation['customer_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt><?= Lang::e('reservation.car') ?></dt><dd><?= htmlspecialchars((string) $reservation['brand'] . ' ' . (string) $reservation['model'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt><?= Lang::e('reservation.pickup') ?></dt><dd><?= htmlspecialchars((string) $reservation['pickup_date'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $reservation['pickup_location_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt><?= Lang::e('reservation.return') ?></dt><dd><?= htmlspecialchars((string) $reservation['return_date'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $reservation['return_location_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt><?= Lang::e('reservation.total') ?></dt><dd class="mono"><?= Formatter::money((float) $reservation['final_amount']) ?></dd>
                </dl>
                <p class="muted"><?= Lang::e('consult.contact_hint') ?></p>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(Contact::whatsappUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.cta_wa') ?></a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php View::partial('partials/public_footer', ['asset' => $asset]); ?>
<script src="<?= $asset('/js/lang-switcher.js') ?>" defer></script>
<script src="<?= $asset('/js/cookie-notice.js') ?>" defer></script>
<script src="<?= $asset('/landing/js/site.js') ?>" defer></script>
<script src="<?= $asset('/landing/js/consult-form.js') ?>" defer></script>
</body>
</html>
