<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $car */
/** @var array<int,array<string,mixed>> $locations */
$c = $car ?? [];
$hex = (string) ($c['color_hex'] ?? '#CCCCCC');
?>
<div class="grid two">
    <div class="field">
        <label class="label" for="license_plate"><?= Lang::e('car.plate') ?></label>
        <input class="input" id="license_plate" name="license_plate" value="<?= htmlspecialchars((string) ($c['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
        <label class="label" for="brand"><?= Lang::e('car.brand') ?></label>
        <input class="input" id="brand" name="brand" required value="<?= htmlspecialchars((string) ($c['brand'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
        <label class="label" for="model"><?= Lang::e('car.model') ?></label>
        <input class="input" id="model" name="model" required value="<?= htmlspecialchars((string) ($c['model'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
        <label class="label" for="year"><?= Lang::e('car.year') ?></label>
        <input class="input" id="year" name="year" type="number" required value="<?= (int) ($c['year'] ?? date('Y')) ?>">
    </div>
    <div class="field">
        <label class="label" for="color"><?= Lang::e('car.color') ?></label>
        <input class="input" id="color" name="color" required value="<?= htmlspecialchars((string) ($c['color'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
        <label class="label" for="color_hex"><?= Lang::e('car.color_hex') ?></label>
        <div class="color-field-row">
            <input type="color" id="color_picker" value="<?= htmlspecialchars($hex, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= Lang::e('car.color_hex') ?>">
            <input class="input mono" id="color_hex" name="color_hex" type="text" value="<?= htmlspecialchars($hex, ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>
    <div class="field">
        <label class="label" for="category"><?= Lang::e('car.category') ?></label>
        <select class="input" id="category" name="category">
            <?php foreach (['economy','standard','suv','luxury','van','truck'] as $cat): ?>
                <option value="<?= $cat ?>" <?= (($c['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= Lang::e('category.' . $cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label" for="seats"><?= Lang::e('car.seats') ?></label>
        <input class="input" id="seats" name="seats" type="number" min="1" value="<?= (int) ($c['seats'] ?? 5) ?>">
    </div>
    <div class="field">
        <label class="label" for="transmission"><?= Lang::e('car.transmission') ?></label>
        <select class="input" id="transmission" name="transmission">
            <?php foreach (['manual','automatic'] as $t): ?>
                <option value="<?= $t ?>" <?= (($c['transmission'] ?? '') === $t) ? 'selected' : '' ?>><?= Lang::e('transmission.' . $t) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label" for="fuel"><?= Lang::e('car.fuel') ?></label>
        <select class="input" id="fuel" name="fuel">
            <?php foreach (['flex','gasoline','diesel','electric','hybrid'] as $f): ?>
                <option value="<?= $f ?>" <?= (($c['fuel'] ?? '') === $f) ? 'selected' : '' ?>><?= Lang::e('fuel.' . $f) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label" for="daily_rate"><?= Lang::e('car.daily_rate') ?></label>
        <input class="input" id="daily_rate" name="daily_rate" type="number" step="0.01" required value="<?= htmlspecialchars((string) ($c['daily_rate'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
        <label class="label" for="status"><?= Lang::e('car.status') ?></label>
        <select class="input" id="status" name="status">
            <?php foreach (['available','rented','maintenance','inactive'] as $s): ?>
                <option value="<?= $s ?>" <?= (($c['status'] ?? '') === $s) ? 'selected' : '' ?>><?= Lang::e('car.' . $s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label" for="location_id"><?= Lang::e('car.location') ?></label>
        <select class="input" id="location_id" name="location_id">
            <option value="0">—</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= (int) $loc['id'] ?>" <?= ((int) ($c['location_id'] ?? 0) === (int) $loc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($loc['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label" for="mileage"><?= Lang::e('car.mileage') ?></label>
        <input class="input" id="mileage" name="mileage" type="number" value="<?= (int) ($c['mileage'] ?? 0) ?>">
    </div>
</div>

<div class="monthly-costs-block">
    <h3 class="form-section-title"><?= Lang::e('car.monthly_expenses') ?></h3>
    <p class="muted form-section-hint"><?= Lang::e('car.monthly_expenses_hint') ?></p>
    <div class="grid three">
        <?php foreach (Car::monthlyExpenseFields() as $field): ?>
        <div class="field">
            <label class="label"><?= Lang::e('car.' . $field) ?></label>
            <input class="input mono" name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) ($c[$field] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="monthly-total-row" aria-live="polite" aria-atomic="true">
        <span class="label monthly-total-label"><?= Lang::e('car.monthly_total_live') ?></span>
        <div id="monthlyTotalLive" class="monthly-total-live mono">$0.00</div>
    </div>
</div>
<script src="<?= htmlspecialchars(Router::url('/js/car-monthly-total.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Router::url('/js/car-form.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>

<div class="grid two">
    <div class="field">
        <label class="label" for="car_image"><?= Lang::e('car.image') ?></label>
        <input class="input" id="car_image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="field">
        <label class="label" for="notes"><?= Lang::e('car.notes') ?></label>
        <textarea class="input" id="notes" name="notes" rows="3"><?= htmlspecialchars((string) ($c['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
</div>
