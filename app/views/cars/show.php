<?php declare(strict_types=1);
/** @var array<string,mixed> $car */
/** @var float|null $myQuota */
/** @var array<int,array<string,mixed>> $carPartners */
?>
<div class="page-head">    <h1 class="page-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= Router::url('/cars') ?>"><?= Lang::e('actions.back') ?></a>
        <?php if (Auth::isOwner()): ?>
            <a class="btn btn-primary" href="<?= Router::url('/cars/' . (int) $car['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>
            <form method="post" action="<?= Router::url('/cars/' . (int) $car['id'] . '/delete') ?>" class="inline-form" data-confirm="<?= Lang::e('confirm.delete') ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-danger"><?= Lang::e('actions.delete') ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>
<div class="grid two">
    <div class="card">
        <?php if (!empty($car['image_url'])): ?>
            <img class="car-photo" src="<?= htmlspecialchars($car['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="">
        <?php endif; ?>
        <dl class="dl">
            <dt><?= Lang::e('car.plate') ?></dt><dd class="mono"><?= htmlspecialchars($car['license_plate'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('car.color') ?></dt><dd><span class="swatch" style="background:<?= htmlspecialchars($car['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span> <?= htmlspecialchars($car['color'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('car.year') ?></dt><dd><?= (int) $car['year'] ?></dd>
            <dt><?= Lang::e('car.daily_rate') ?></dt><dd class="mono"><?= Formatter::money((float) $car['daily_rate']) ?></dd>
            <dt><?= Lang::e('car.status') ?></dt><dd><?= Ui::carStatusBadge((string) $car['status']) ?></dd>
            <?php if (Auth::isPartner() && $myQuota !== null): ?>
            <dt><?= Lang::e('partner.my_quota') ?></dt><dd class="mono"><?= number_format($myQuota, 2, ',', '.') ?>%</dd>
            <?php endif; ?>
            <dt><?= Lang::e('car.location') ?></dt><dd><?= htmlspecialchars((string) ($car['location_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>        </dl>
    </div>
    <div class="card">
        <?php if (Auth::isOwner()): ?>
        <h2 class="card-title"><?= Lang::e('car.monthly_expenses') ?></h2>        <dl class="dl">
            <dt><?= Lang::e('car.monthly_fuel') ?></dt><dd class="mono"><?= Formatter::money((float) ($car['monthly_fuel'] ?? 0)) ?></dd>
            <dt><?= Lang::e('car.monthly_toll') ?></dt><dd class="mono"><?= Formatter::money((float) ($car['monthly_toll'] ?? 0)) ?></dd>
            <dt><?= Lang::e('car.monthly_wash') ?></dt><dd class="mono"><?= Formatter::money((float) ($car['monthly_wash'] ?? 0)) ?></dd>
            <dt><?= Lang::e('car.monthly_maintenance') ?></dt><dd class="mono"><?= Formatter::money((float) ($car['monthly_maintenance'] ?? 0)) ?></dd>
            <dt><?= Lang::e('car.monthly_extra') ?></dt><dd class="mono"><?= Formatter::money((float) ($car['monthly_extra'] ?? 0)) ?></dd>
            <dt><strong><?= Lang::e('car.monthly_total') ?></strong></dt><dd class="mono"><strong><?= Formatter::money(Car::monthlyExpensesTotal($car)) ?></strong></dd>
        </dl>
        <?php else: ?>
        <h2 class="card-title"><?= Lang::e('partner.my_quota') ?></h2>
        <p class="mono quota-highlight"><?= number_format((float) ($myQuota ?? 0), 2, ',', '.') ?>%</p>
        <p class="muted"><?= Lang::e('partner.quota_only_yours') ?></p>
        <?php endif; ?>
    </div>
    <?php if (Auth::isOwner() && $carPartners !== []): ?>
    <div class="card span-full">
        <h2 class="card-title"><?= Lang::e('partner.owners_on_vehicle') ?></h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th><?= Lang::e('customer.name') ?></th><th><?= Lang::e('partner.quota_percent') ?></th></tr></thead>
                <tbody>
                <?php foreach ($carPartners as $cp): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $cp['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="mono"><?= number_format((float) ($cp['quota_percent'] ?? 0), 2, ',', '.') ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>    <div class="card span-full">
        <h2 class="card-title"><?= Lang::e('car.notes') ?></h2>
        <p class="muted"><?= nl2br(htmlspecialchars((string) ($car['notes'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    </div>
</div>
