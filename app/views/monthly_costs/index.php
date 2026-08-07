<?php declare(strict_types=1);
/** @var list<array<string,mixed>> $cars */
/** @var array<string,float> $categoryTotals */
/** @var float $fleetTotal */
/** @var list<string> $fields */
/** @var array<string,mixed>|null $selectedCar */
/** @var int $selectedId */
/** @var bool $canEdit */
$isOwner = Auth::isOwner();
$canEdit = $canEdit ?? $isOwner;
$selectedCar = $selectedCar ?? null;
$selectedId = (int) ($selectedId ?? 0);
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.monthly_costs') ?></h1>
</div>

<p class="muted page-lead"><?= Lang::e('monthly_costs.hint') ?></p>

<div class="card mt">
    <div class="monthly-fleet-total">
        <span class="label"><?= Lang::e('monthly_costs.fleet_total') ?></span>
        <strong class="mono monthly-fleet-total-value"><?= htmlspecialchars(Formatter::moneyWithBrl($fleetTotal), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('monthly_costs.fill_title') ?></h2>
    <?php if ($cars === []): ?>
        <p class="muted"><?= Lang::e('table.empty') ?></p>
    <?php else: ?>
        <form class="filters-row" method="get" action="<?= Router::url('/monthly-costs') ?>" style="margin-bottom: 1rem;">
            <label class="label" for="monthly-car-select"><?= Lang::e('monthly_costs.select_car') ?></label>
            <select class="input" id="monthly-car-select" name="car_id" onchange="this.form.submit()">
                <?php foreach ($cars as $car): ?>
                    <?php
                    $id = (int) ($car['id'] ?? 0);
                    $label = trim((string) ($car['brand'] ?? '') . ' ' . (string) ($car['model'] ?? ''));
                    $plate = trim((string) ($car['license_plate'] ?? ''));
                    if ($plate !== '') {
                        $label .= ' · ' . $plate;
                    }
                    ?>
                    <option value="<?= $id ?>" <?= $selectedId === $id ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedCar !== null): ?>
            <?php if ($canEdit): ?>
                <form class="form-stack" method="post" action="<?= Router::url('/monthly-costs/update') ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="car_id" value="<?= $selectedId ?>">
                    <?php View::partial('partials/monthly_expenses_fields', ['c' => $selectedCar]); ?>
                    <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
                </form>
            <?php else: ?>
                <?php View::partial('partials/monthly_expenses_fields', ['c' => $selectedCar]); ?>
                <p class="muted mt"><?= Lang::e('monthly_costs.readonly') ?></p>
                <script>
                  document.querySelectorAll('.monthly-costs-block input').forEach(function (el) { el.disabled = true; });
                </script>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('monthly_costs.by_category') ?></h2>
    <div class="grid three">
        <?php foreach ($fields as $field): ?>
            <?php $amount = (float) ($categoryTotals[$field] ?? 0); ?>
            <div class="field">
                <span class="label"><?= Lang::e('car.' . $field) ?></span>
                <div class="mono"><?= htmlspecialchars(Formatter::moneyWithBrl($amount), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('monthly_costs.by_car') ?></h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= Lang::e('reservation.car') ?></th>
                    <th><?= Lang::e('car.plate') ?></th>
                    <th><?= Lang::e('car.status') ?></th>
                    <th><?= Lang::e('car.monthly_total') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cars as $car): ?>
                <?php
                $label = trim((string) ($car['brand'] ?? '') . ' ' . (string) ($car['model'] ?? ''));
                $total = Car::monthlyExpensesTotal($car);
                $carId = (int) ($car['id'] ?? 0);
                ?>
                <tr>
                    <td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="mono" data-label="<?= Lang::e('car.plate') ?>"><?= htmlspecialchars((string) ($car['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('car.status') ?>"><?= Ui::carStatusBadge((string) ($car['status'] ?? '')) ?></td>
                    <td class="mono" data-label="<?= Lang::e('car.monthly_total') ?>"><?= htmlspecialchars(Formatter::moneyWithBrl($total), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="">
                        <a class="btn btn-sm btn-secondary" href="<?= Router::url('/monthly-costs?car_id=' . $carId) ?>"><?= Lang::e('actions.edit') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($cars === []): ?>
                <tr><td colspan="5" class="muted"><?= Lang::e('table.empty') ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
