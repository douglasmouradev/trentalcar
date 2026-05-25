<?php declare(strict_types=1); /** @var array<string,mixed> $car */ ?>
<div class="page-head">
    <h1 class="page-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= Router::url('/cars') ?>"><?= Lang::e('actions.back') ?></a>
        <?php if (Auth::isOwner()): ?>
            <a class="btn btn-primary" href="<?= Router::url('/cars/' . (int) $car['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>
            <form method="post" action="<?= Router::url('/cars/' . (int) $car['id'] . '/delete') ?>" onsubmit="return confirm('OK?');" class="inline-form">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-danger"><?= Lang::e('actions.delete') ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>
<div class="grid two">
    <div class="card">
        <?php if (!empty($car['image_url'])): ?>
            <img class="car-photo" src="<?= htmlspecialchars($car['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <dl class="dl">
            <dt><?= Lang::e('car.plate') ?></dt><dd class="mono"><?= htmlspecialchars($car['license_plate'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('car.color') ?></dt><dd><span class="swatch" style="background:<?= htmlspecialchars($car['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span> <?= htmlspecialchars($car['color'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('car.year') ?></dt><dd><?= (int) $car['year'] ?></dd>
            <dt><?= Lang::e('car.daily_rate') ?></dt><dd class="mono">R$ <?= number_format((float) $car['daily_rate'], 2, ',', '.') ?></dd>
            <dt><?= Lang::e('car.status') ?></dt><dd><?= htmlspecialchars($car['status'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('car.location') ?></dt><dd><?= htmlspecialchars((string) ($car['location_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </div>
    <div class="card">
        <h2 class="card-title"><?= Lang::e('car.monthly_expenses') ?></h2>
        <dl class="dl">
            <dt><?= Lang::e('car.monthly_fuel') ?></dt><dd class="mono">R$ <?= number_format((float) ($car['monthly_fuel'] ?? 0), 2, ',', '.') ?></dd>
            <dt><?= Lang::e('car.monthly_toll') ?></dt><dd class="mono">R$ <?= number_format((float) ($car['monthly_toll'] ?? 0), 2, ',', '.') ?></dd>
            <dt><?= Lang::e('car.monthly_wash') ?></dt><dd class="mono">R$ <?= number_format((float) ($car['monthly_wash'] ?? 0), 2, ',', '.') ?></dd>
            <dt><?= Lang::e('car.monthly_maintenance') ?></dt><dd class="mono">R$ <?= number_format((float) ($car['monthly_maintenance'] ?? 0), 2, ',', '.') ?></dd>
            <dt><?= Lang::e('car.monthly_extra') ?></dt><dd class="mono">R$ <?= number_format((float) ($car['monthly_extra'] ?? 0), 2, ',', '.') ?></dd>
            <dt><strong><?= Lang::e('car.monthly_total') ?></strong></dt><dd class="mono"><strong>R$ <?= number_format(Car::monthlyExpensesTotal($car), 2, ',', '.') ?></strong></dd>
        </dl>
    </div>
    <div class="card span-full">
        <h2 class="card-title"><?= Lang::e('car.notes') ?></h2>
        <p class="muted"><?= nl2br(htmlspecialchars((string) ($car['notes'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    </div>
</div>
