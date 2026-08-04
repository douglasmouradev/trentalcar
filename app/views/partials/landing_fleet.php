<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $fleetCars */
$fleetCars = $fleetCars ?? [];
$asset = static fn (string $path): string => htmlspecialchars(Router::url($path), ENT_QUOTES, 'UTF-8');
?>
<?php if ($fleetCars === []): ?>
    <p class="muted lp-fleet-empty"><?= Lang::e('empty.cars.title') ?></p>
<?php else: ?>
    <?php foreach ($fleetCars as $car): ?>
        <?php
        $filterKey = Car::landingFilterKey((string) ($car['category'] ?? 'standard'));
    $img = Car::publicImageUrl(!empty($car['image_url']) ? (string) $car['image_url'] : null);
        $title = htmlspecialchars((string) $car['brand'] . ' ' . (string) $car['model'], ENT_QUOTES, 'UTF-8');
        $rate = Formatter::moneyWithBrl((float) ($car['daily_rate'] ?? 0));
        ?>
        <article class="lp-car" data-category="<?= htmlspecialchars($filterKey, ENT_QUOTES, 'UTF-8') ?>">
            <div class="lp-car-img">
                <?php if (($car['status'] ?? '') === 'available'): ?>
                <span class="lp-car-badge"><?= Lang::e('car.available') ?></span>
                <?php endif; ?>
                <img src="<?= $img ?>" alt="<?= $title ?>" width="360" height="230" loading="lazy">
            </div>
            <div class="lp-car-body">
                <p class="lp-car-group"><?= Lang::e('category.' . ($car['category'] ?? 'standard')) ?></p>
                <h3><?= $title ?></h3>
                <ul class="lp-car-specs">
                    <li><?= (int) ($car['seats'] ?? 5) ?> <?= Lang::e('car.seats') ?></li>
                    <li><?= Lang::e('transmission.' . ($car['transmission'] ?? 'automatic')) ?></li>
                    <li class="mono"><?= htmlspecialchars((string) ($car['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
                <p class="lp-car-price"><?= Lang::e('landing.car_price_from') ?> <strong><?= $rate ?></strong> <span><?= Lang::e('landing.car_per_day') ?></span></p>
                <p class="lp-car-disclaimer"><?= Lang::e('landing.car_disclaimer') ?></p>
                <a class="btn btn-block btn-primary" href="#reserva" data-car-id="<?= (int) $car['id'] ?>" data-car-label="<?= $title ?>"><?= Lang::e('landing.car_cta') ?></a>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
