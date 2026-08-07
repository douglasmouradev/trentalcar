<?php declare(strict_types=1);
/** @var array<string,mixed> $c */
$c = $c ?? [];
?>
<div class="monthly-costs-block">
    <h3 class="form-section-title"><?= Lang::e('car.monthly_expenses') ?></h3>
    <p class="muted form-section-hint"><?= Lang::e('car.monthly_expenses_hint') ?></p>
    <div class="grid three">
        <?php foreach (Car::monthlyExpenseFields() as $field): ?>
        <?php
            $monthlyVal = (float) ($c[$field] ?? 0);
            $monthlyDisplay = $monthlyVal > 0 ? (string) $monthlyVal : '';
        ?>
        <div class="field">
            <label class="label" for="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('car.' . $field) ?></label>
            <input class="input mono" id="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" type="number" step="0.01" min="0" inputmode="decimal" placeholder="0" value="<?= htmlspecialchars($monthlyDisplay, ENT_QUOTES, 'UTF-8') ?>" data-usd-convert data-monthly-expense>
            <div class="field-fx muted mono" data-usd-convert-out aria-live="polite"><?= htmlspecialchars(Formatter::moneyWithBrl($monthlyVal), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="monthly-total-row" aria-live="polite" aria-atomic="true">
        <span class="label monthly-total-label"><?= Lang::e('car.monthly_total_live') ?></span>
        <div id="monthlyTotalLive" class="monthly-total-live mono"><?= htmlspecialchars(Formatter::moneyWithBrl(Car::monthlyExpensesTotal($c)), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</div>
<script src="<?= htmlspecialchars(Asset::url('/js/car-monthly-total.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
