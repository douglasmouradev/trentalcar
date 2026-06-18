<?php declare(strict_types=1);
/** @var array<string,mixed>|null $reservation */
/** @var string|null $error */
$asset = static fn (string $path): string => htmlspecialchars(Router::url($path), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(str_replace('_', '-', Lang::locale()), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/landing/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="lp-body lp-body--booking">
<main class="lp-section lp-section--wide lp-booking-page" style="padding-top:3rem">
    <header class="lp-section-head">
        <h1><?= Lang::e('consult.title') ?></h1>
        <p class="lp-section-lead"><?= Lang::e('consult.lead') ?></p>
    </header>
    <?php if (!empty($error)): ?>
        <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="lp-date-filter card" method="post" action="<?= $asset('/consultar') ?>">
        <?= Csrf::field() ?>
        <div class="lp-booking-grid lp-booking-grid--contact">
            <label class="lp-field">
                <span class="lp-label"><?= Lang::e('reservation.code') ?></span>
                <input class="lp-input mono" type="text" name="code" required maxlength="20" autocomplete="off">
            </label>
            <label class="lp-field">
                <span class="lp-label"><?= Lang::e('auth.email') ?></span>
                <input class="lp-input" type="email" name="email" required maxlength="180" autocomplete="email">
            </label>
            <div class="lp-field lp-field--btn">
                <span class="lp-label lp-label--ghost" aria-hidden="true">&nbsp;</span>
                <button class="btn btn-primary" type="submit"><?= Lang::e('consult.submit') ?></button>
            </div>
        </div>
    </form>
    <?php if ($reservation !== null): ?>
        <div class="card mt" style="margin-top:1.5rem;background:#fff;padding:1.25rem;border-radius:14px">
            <h2 class="card-title mono"><?= htmlspecialchars((string) $reservation['code'], ENT_QUOTES, 'UTF-8') ?></h2>
            <dl class="dl">
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
    <p style="margin-top:2rem"><a href="<?= $asset('/') ?>"><?= Lang::e('booking.back_home') ?></a></p>
</main>
</body>
</html>
