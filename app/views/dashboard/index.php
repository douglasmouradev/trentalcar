<?php declare(strict_types=1);

/** @var bool $isOwner */

/** @var bool $isPartner */

/** @var array<int,array<string,mixed>> $partnerCars */

/** @var int $partnerActiveRes */

/** @var float $revenueMonth */

/** @var float $revenuePrevMonth */

/** @var string|null $revenueDelta */

/** @var int $fleet */

/** @var int $activeRes */

/** @var int $occupancy */

/** @var int $unpaid */

/** @var array<string,int> $chartDays */

/** @var array<int,array<string,mixed>> $revenueByCategory */

/** @var array<int,array<string,mixed>> $returns */

/** @var array<int,array<string,mixed>> $maintenance */

/** @var array<int,array<string,mixed>> $myToday */

/** @var int $myTodayCount */

$userName = (string) (Auth::user()['name'] ?? '');

$todayLabel = date('d/m/Y');

?>

<div class="dashboard-page">

<div class="page-head page-head--dashboard">
    <div class="page-head-text">
        <h1 class="page-title"><?= Lang::e('nav.dashboard') ?></h1>
        <p class="dashboard-subtitle"><?= Lang::e('dashboard.subtitle', ['date' => $todayLabel]) ?></p>
    </div>
    <?php if ($isOwner): ?>
    <div class="page-actions quick-actions-bar" aria-label="<?= Lang::e('dashboard.quick_actions') ?>">
        <a class="btn btn-primary btn-sm" href="<?= Router::url('/reservations/create') ?>"><?= Lang::e('dashboard.quick_reservation') ?></a>
        <a class="btn btn-secondary btn-sm" href="<?= Router::url('/customers/create') ?>"><?= Lang::e('dashboard.quick_customer') ?></a>
        <a class="btn btn-ghost btn-sm" href="<?= Router::url('/reservations/calendar') ?>"><?= Lang::e('dashboard.quick_calendar') ?></a>
    </div>
    <?php endif; ?>
</div>

<?php View::partial('partials/dashboard_alerts', ['alerts' => $alerts ?? []]); ?>



<?php if (!$isOwner): ?>

    <div class="grid kpis">

        <div class="card kpi"><div class="kpi-label"><?= Lang::e('dashboard.operator_today') ?></div><div class="kpi-value"><?= (int) $myTodayCount ?></div></div>

        <div class="card kpi"><div class="kpi-label"><?= Lang::e('dashboard.operator_queue') ?></div><div class="kpi-value"><?= count($myToday) ?></div></div>

    </div>

    <div class="card mt">

        <h2 class="card-title"><?= Lang::e('dashboard.operator_upcoming') ?></h2>

        <div class="table-wrap">

            <table class="table table--responsive">

                <thead><tr><th><?= Lang::e('reservation.code') ?></th><th><?= Lang::e('reservation.customer') ?></th><th><?= Lang::e('reservation.car') ?></th><th><?= Lang::e('reservation.pickup') ?></th><th></th></tr></thead>

                <tbody>

                <?php foreach ($myToday as $row): ?>

                    <tr>

                        <td class="mono" data-label="<?= Lang::e('reservation.code') ?>"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="<?= Lang::e('reservation.customer') ?>"><?= htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="<?= Lang::e('reservation.car') ?>"><?= htmlspecialchars($row['brand'] . ' ' . $row['model'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="<?= Lang::e('reservation.pickup') ?>"><?= htmlspecialchars($row['pickup_date'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/reservations/' . (int) $row['id']) ?>"><?= Lang::e('actions.view') ?></a></td>

                    </tr>

                <?php endforeach; ?>

                <?php if ($myToday === []): ?><tr><td colspan="5" class="muted"><?= Lang::e('dashboard.operator_empty') ?></td></tr><?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php else: ?>

    <div class="grid kpis">
        <div class="card kpi kpi--revenue">
            <div class="kpi-label"><?= Lang::e('dashboard.revenue_month') ?></div>
            <div class="kpi-value"><?= Formatter::moneyWithBrl($revenueMonth) ?></div>
            <?php if (!empty($revenueDelta)): ?>
                <div class="kpi-delta"><?= htmlspecialchars($revenueDelta, ENT_QUOTES, 'UTF-8') ?> <?= Lang::e('dashboard.revenue_delta') ?></div>
            <?php endif; ?>
        </div>
        <div class="card kpi"><div class="kpi-label"><?= Lang::e('dashboard.fleet') ?></div><div class="kpi-value"><?= (int) $fleet ?></div></div>
        <div class="card kpi"><div class="kpi-label"><?= Lang::e('dashboard.active_res') ?></div><div class="kpi-value"><?= (int) $activeRes ?></div></div>
        <div class="card kpi"><div class="kpi-label"><?= Lang::e('dashboard.occupancy') ?></div><div class="kpi-value"><?= (int) $occupancy ?>%</div></div>
        <div class="card kpi kpi--warn"><div class="kpi-label"><?= Lang::e('dashboard.unpaid') ?></div><div class="kpi-value"><?= (int) $unpaid ?></div></div>
    </div>



    <div class="grid two mt">

        <div class="card card--chart">

            <h2 class="card-title"><?= Lang::e('dashboard.chart_reservations') ?></h2>

            <div class="bar-chart" role="img" aria-label="<?= Lang::e('dashboard.chart_reservations') ?>">

                <?php

                $max = max(1, ...array_values($chartDays));

                foreach ($chartDays as $day => $c):

                    $h = (int) round(($c / $max) * 100);

                    $dayLabel = substr((string) $day, -2);

                    ?>

                    <div class="bar-col" title="<?= htmlspecialchars($day . ': ' . $c, ENT_QUOTES, 'UTF-8') ?>">

                        <span class="bar-value"><?= (int) $c ?></span>

                        <div class="bar">

                            <div class="bar-fill" style="height:<?= $h ?>%"></div>

                        </div>

                        <span class="bar-label"><?= htmlspecialchars($dayLabel, ENT_QUOTES, 'UTF-8') ?></span>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <div class="card">

            <h2 class="card-title"><?= Lang::e('dashboard.chart_category') ?></h2>

            <ul class="list-plain category-list">

                <?php foreach ($revenueByCategory as $cat): ?>

                    <li><span class="mono"><?= Ui::categoryLabel((string) $cat['category']) ?></span> <span class="category-amount"><?= Formatter::moneyWithBrl((float) $cat['total']) ?></span></li>

                <?php endforeach; ?>

                <?php if ($revenueByCategory === []): ?><li class="muted"><?= Lang::e('table.empty') ?></li><?php endif; ?>

            </ul>

        </div>

    </div>



    <div class="grid two mt">

        <div class="card">

            <h2 class="card-title"><?= Lang::e('dashboard.returns') ?></h2>

            <div class="table-wrap">

                <table class="table table--responsive">

                    <thead><tr><th><?= Lang::e('reservation.code') ?></th><th><?= Lang::e('reservation.customer') ?></th><th><?= Lang::e('car.plate') ?></th><th><?= Lang::e('reservation.return') ?></th><th></th></tr></thead>

                    <tbody>

                    <?php foreach ($returns as $row): ?>

                        <tr>

                            <td class="mono" data-label="<?= Lang::e('reservation.code') ?>"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td data-label="<?= Lang::e('reservation.customer') ?>"><?= htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td class="mono" data-label="<?= Lang::e('car.plate') ?>"><?= htmlspecialchars((string) ($row['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>

                            <td data-label="<?= Lang::e('reservation.return') ?>"><?= htmlspecialchars($row['return_date'] . ' ' . substr((string) $row['return_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>

                            <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/reservations/' . (int) $row['id']) ?>"><?= Lang::e('actions.view') ?></a></td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if ($returns === []): ?><tr><td colspan="5" class="muted"><?= Lang::e('dashboard.returns_empty') ?></td></tr><?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <div class="card">

            <h2 class="card-title"><?= Lang::e('dashboard.maintenance') ?></h2>

            <div class="table-wrap">

                <table class="table table--responsive">

                    <thead><tr><th><?= Lang::e('car.model') ?></th><th><?= Lang::e('car.plate') ?></th><th><?= Lang::e('car.color') ?></th><th></th></tr></thead>

                    <tbody>

                    <?php foreach ($maintenance as $car): ?>

                        <tr>

                            <td data-label="<?= Lang::e('car.model') ?>"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td class="mono" data-label="<?= Lang::e('car.plate') ?>"><?= htmlspecialchars((string) ($car['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>

                            <td data-label="<?= Lang::e('car.color') ?>"><span class="swatch" style="background:<?= htmlspecialchars($car['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span> <?= htmlspecialchars($car['color'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/cars/' . (int) $car['id']) ?>"><?= Lang::e('actions.view') ?></a></td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if ($maintenance === []): ?><tr><td colspan="4" class="muted"><?= Lang::e('dashboard.maintenance_empty') ?></td></tr><?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

<?php endif; ?>

</div>


