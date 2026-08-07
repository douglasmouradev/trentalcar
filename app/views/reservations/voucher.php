<?php declare(strict_types=1); /** @var array<string,mixed> $r */ ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(str_replace('_', '-', Lang::locale()), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <?php View::partial('partials/favicon'); ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(Router::url('/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script src="<?= htmlspecialchars(Router::url('/js/voucher.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <style>
        @media print { .no-print { display: none !important; } body { background: #fff; } }
        .voucher { max-width: 720px; margin: 2rem auto; padding: 2rem; }
        .voucher-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .voucher-code { font-size: 1.5rem; font-weight: 700; font-family: var(--font-mono, monospace); }
    </style>
</head>
<body class="theme-titanium" data-usd-brl-rate="<?= htmlspecialchars((string) ExchangeRate::rate(), ENT_QUOTES, 'UTF-8') ?>">
<div class="voucher card">
    <div class="voucher-head">
        <div>
            <div class="brand-title"><?= Lang::e('app.name') ?></div>
            <div class="muted"><?= Lang::e('reservation.voucher') ?></div>
        </div>
        <div class="voucher-code"><?= htmlspecialchars((string) $r['code'], ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <dl class="dl">
        <dt><?= Lang::e('reservation.customer') ?></dt>
        <dd><?= htmlspecialchars((string) $r['customer_name'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(Formatter::document((string) $r['customer_document']), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('reservation.car') ?></dt>
        <dd><?= htmlspecialchars($r['brand'] . ' ' . $r['model'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $r['license_plate'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('reservation.pickup') ?></dt>
        <dd><?= htmlspecialchars($r['pickup_date'] . ' ' . substr((string) $r['pickup_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(LeadPickupOptions::withHotelName((string) $r['pickup_location_name'], (string) ($r['pickup_hotel_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('reservation.return') ?></dt>
        <dd><?= htmlspecialchars($r['return_date'] . ' ' . substr((string) $r['return_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(LeadPickupOptions::withHotelName((string) $r['return_location_name'], (string) ($r['return_hotel_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('reservation.status') ?></dt>
        <dd><?= Lang::e('status.' . $r['status']) ?></dd>
        <dt><?= Lang::e('reservation.total') ?></dt>
        <dd class="mono"><?= Formatter::moneyWithBrl((float) $r['final_amount']) ?></dd>
    </dl>
    <p class="muted"><?= Lang::e('reservation.voucher_footer') ?></p>
    <p class="no-print"><button class="btn btn-primary" type="button" id="voucher-print"><?= Lang::e('reservation.voucher_print') ?></button>
        <a class="btn btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id']) ?>"><?= Lang::e('actions.back') ?></a></p>
</div>
</body>
</html>
