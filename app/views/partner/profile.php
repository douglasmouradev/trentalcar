<?php declare(strict_types=1);
/** @var array<string,mixed> $user */
/** @var array<int,array<string,mixed>> $assignments */
/** @var array<int,array<string,mixed>> $revenueByCar */
/** @var float $revenueMonth */
/** @var array<int,array{month:string,revenue:float}> $revenueHistory */
/** @var array<int,array<string,mixed>> $reservations */
$revenueMap = [];
foreach ($revenueByCar as $row) {
    $revenueMap[(int) $row['car_id']] = (float) ($row['revenue_share'] ?? 0);
}
$maxRev = max(1.0, ...array_map(static fn (array $m): float => (float) ($m['revenue'] ?? 0), $revenueHistory ?: [['revenue' => 1]]));
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('partner.my_profile') ?></h1>
    <form method="post" action="<?= htmlspecialchars(Router::url('/partner/profile/export'), ENT_QUOTES, 'UTF-8') ?>" class="inline-form">
        <?= Csrf::field() ?>
        <button class="btn btn-secondary" type="submit"><?= Lang::e('partner.export_csv') ?></button>
    </form>
</div>
<p class="muted"><?= Lang::e('partner.profile_intro') ?></p>

<div class="grid kpis">
    <div class="card kpi"><div class="kpi-label"><?= Lang::e('partner.my_vehicles') ?></div><div class="kpi-value"><?= count($assignments) ?></div></div>
    <div class="card kpi"><div class="kpi-label"><?= Lang::e('partner.revenue_month') ?></div><div class="kpi-value"><?= Formatter::money($revenueMonth) ?></div></div>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('partner.revenue_history') ?></h2>
    <div class="bar-chart" role="img" aria-label="<?= Lang::e('partner.revenue_history') ?>">
        <?php foreach ($revenueHistory as $m): ?>
            <?php $h = max(4, (int) round(((float) $m['revenue'] / $maxRev) * 100)); ?>
            <div class="bar-chart-col" title="<?= htmlspecialchars($m['month'] . ': ' . Formatter::money((float) $m['revenue']), ENT_QUOTES, 'UTF-8') ?>">
                <div class="bar-chart-bar" style="height:<?= $h ?>%"></div>
                <span class="bar-chart-label"><?= htmlspecialchars(substr($m['month'], 5), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('partner.my_data') ?></h2>
    <dl class="dl">
        <dt><?= Lang::e('customer.name') ?></dt><dd><?= htmlspecialchars((string) $user['name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('auth.email') ?></dt><dd class="mono"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('customer.phone') ?></dt><dd><?= htmlspecialchars(Formatter::phone((string) ($user['phone'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('partner.my_quotas') ?></h2>
    <?php if ($assignments === []): ?>
        <p class="muted"><?= Lang::e('dashboard.partner_no_cars') ?></p>
    <?php else: ?>
    <div class="table-wrap table--responsive">
        <table class="table">
            <thead>
            <tr>
                <th><?= Lang::e('car.model') ?></th>
                <th><?= Lang::e('car.plate') ?></th>
                <th><?= Lang::e('car.status') ?></th>
                <th><?= Lang::e('partner.quota_percent') ?></th>
                <th><?= Lang::e('partner.revenue_share') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <?php $cid = (int) $a['car_id']; ?>
                <tr>
                    <td data-label="<?= Lang::e('car.model') ?>"><?= htmlspecialchars((string) $a['brand'] . ' ' . (string) $a['model'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('car.plate') ?>" class="mono"><?= htmlspecialchars((string) $a['license_plate'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('car.status') ?>"><?= Ui::carStatusBadge((string) $a['status']) ?></td>
                    <td data-label="<?= Lang::e('partner.quota_percent') ?>" class="mono"><?= number_format((float) ($a['quota_percent'] ?? 0), 2, ',', '.') ?>%</td>
                    <td data-label="<?= Lang::e('partner.revenue_share') ?>" class="mono"><?= Formatter::money($revenueMap[$cid] ?? 0.0) ?></td>
                    <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/cars/' . $cid) ?>"><?= Lang::e('actions.view') ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('partner.recent_reservations') ?></h2>
    <?php if ($reservations === []): ?>
        <p class="muted"><?= Lang::e('empty.reservations.title') ?></p>
    <?php else: ?>
    <div class="table-wrap table--responsive">
        <table class="table">
            <thead><tr><th><?= Lang::e('reservation.code') ?></th><th><?= Lang::e('reservation.customer') ?></th><th><?= Lang::e('car.model') ?></th><th><?= Lang::e('partner.revenue_share') ?></th><th><?= Lang::e('reservation.status') ?></th></tr></thead>
            <tbody>
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td data-label="<?= Lang::e('reservation.code') ?>" class="mono"><?= htmlspecialchars((string) $r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('reservation.customer') ?>"><?= htmlspecialchars((string) $r['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('car.model') ?>"><?= htmlspecialchars((string) $r['brand'] . ' ' . (string) $r['model'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('partner.revenue_share') ?>" class="mono"><?= Formatter::money((float) ($r['share_amount'] ?? 0)) ?></td>
                    <td data-label="<?= Lang::e('reservation.status') ?>"><?= Ui::statusBadge((string) $r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
