<?php declare(strict_types=1);
/** @var array<string,mixed> $costs */
/** @var list<string> $fields */
/** @var float $total */
/** @var bool $canEdit */
$canEdit = $canEdit ?? Auth::isOwner();
$costs = $costs ?? FixedCost::defaults();
$fields = $fields ?? FixedCost::fields();
$total = $total ?? 0.0;
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.fixed_costs') ?></h1>
</div>

<?php View::partial('partials/costs_tabs', ['activeTab' => 'fixed']); ?>

<p class="muted page-lead"><?= Lang::e('fixed_costs.hint') ?></p>

<div class="card mt">
    <div class="monthly-fleet-total">
        <span class="label"><?= Lang::e('fixed_costs.total') ?></span>
        <strong class="mono monthly-fleet-total-value"><?= htmlspecialchars(Formatter::moneyWithBrl($total), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</div>

<div class="card mt">
    <h2 class="card-title"><?= Lang::e('fixed_costs.fill_title') ?></h2>
    <?php if (!Schema::hasTable('fixed_costs')): ?>
        <p class="muted"><?= Lang::e('fixed_costs.migration_required') ?></p>
    <?php elseif ($canEdit): ?>
        <form class="form-stack" method="post" action="<?= Router::url('/fixed-costs/update') ?>">
            <?= Csrf::field() ?>
            <div class="grid three">
                <?php foreach ($fields as $field): ?>
                    <?php
                    $val = (float) ($costs[$field] ?? 0);
                    $display = $val > 0 ? (string) $val : '';
                    ?>
                    <div class="field">
                        <label class="label" for="fixed_<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e(FixedCost::langKey($field)) ?></label>
                        <input class="input mono" id="fixed_<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" type="number" step="0.01" min="0" inputmode="decimal" placeholder="0" value="<?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8') ?>" data-usd-convert data-monthly-expense>
                        <div class="field-fx muted mono" data-usd-convert-out aria-live="polite"><?= htmlspecialchars(Formatter::moneyWithBrl($val), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="monthly-total-row" aria-live="polite" aria-atomic="true">
                <span class="label monthly-total-label"><?= Lang::e('car.monthly_total_live') ?></span>
                <div id="monthlyTotalLive" class="monthly-total-live mono"><?= htmlspecialchars(Formatter::moneyWithBrl($total), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
        </form>
        <script src="<?= htmlspecialchars(Asset::url('/js/car-monthly-total.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php else: ?>
        <div class="grid three">
            <?php foreach ($fields as $field): ?>
                <?php $val = (float) ($costs[$field] ?? 0); ?>
                <div class="field">
                    <span class="label"><?= Lang::e(FixedCost::langKey($field)) ?></span>
                    <div class="mono"><?= htmlspecialchars(Formatter::moneyWithBrl($val), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="muted mt"><?= Lang::e('fixed_costs.readonly') ?></p>
    <?php endif; ?>
</div>
