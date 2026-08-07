<?php declare(strict_types=1); /** @var array<string,mixed> $r */ /** @var array<int,array<string,mixed>> $inspections */ ?>

<div class="page-head">

    <h1 class="page-title mono"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="page-actions">

        <a class="btn btn-secondary" href="<?= Router::url('/reservations') ?>"><?= Lang::e('actions.back') ?></a>

        <a class="btn btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id'] . '/voucher') ?>" target="_blank" rel="noopener"><?= Lang::e('reservation.voucher') ?></a>

        <a class="btn btn-primary" href="<?= Router::url('/reservations/' . (int) $r['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>

        <?php if ($r['status'] !== 'cancelled' && $r['status'] !== 'completed'): ?>

            <form method="post" action="<?= Router::url('/reservations/' . (int) $r['id'] . '/cancel') ?>" class="inline-form" data-confirm="<?= Lang::e('confirm.cancel_reservation') ?>">

                <?= Csrf::field() ?>

                <button type="submit" class="btn btn-danger"><?= Lang::e('reservation.cancel_btn') ?></button>

            </form>

        <?php endif; ?>

    </div>

</div>

<div class="card mt">

    <?php View::partial('partials/status_timeline', ['current' => (string) $r['status']]); ?>

</div>

<div class="grid two">

    <div class="card">

        <h2 class="card-title"><?= Lang::e('reservation.customer') ?></h2>

        <p><?= htmlspecialchars($r['customer_name'], ENT_QUOTES, 'UTF-8') ?> <span class="mono muted"><?= htmlspecialchars(Formatter::document((string) $r['customer_document']), ENT_QUOTES, 'UTF-8') ?></span></p>

        <h2 class="card-title"><?= Lang::e('reservation.car') ?></h2>

        <p><span class="swatch" style="background:<?= htmlspecialchars($r['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span>

            <?= htmlspecialchars($r['brand'] . ' ' . $r['model'], ENT_QUOTES, 'UTF-8') ?> — <span class="mono"><?= htmlspecialchars((string) ($r['license_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></p>

        <h2 class="card-title"><?= Lang::e('reservation.operator') ?></h2>

        <p><?= htmlspecialchars($r['operator_name'], ENT_QUOTES, 'UTF-8') ?></p>

    </div>

    <div class="card">

        <dl class="dl">

            <dt><?= Lang::e('reservation.pickup') ?></dt>

            <dd><?= htmlspecialchars($r['pickup_date'] . ' ' . substr((string) $r['pickup_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(LeadPickupOptions::withHotelName((string) $r['pickup_location_name'], (string) ($r['pickup_hotel_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd>

            <dt><?= Lang::e('reservation.return') ?></dt>

            <dd><?= htmlspecialchars($r['return_date'] . ' ' . substr((string) $r['return_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(LeadPickupOptions::withHotelName((string) $r['return_location_name'], (string) ($r['return_hotel_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd>

            <dt><?= Lang::e('reservation.status') ?></dt>

            <dd><?= Ui::statusBadge((string) $r['status']) ?></dd>

            <dt><?= Lang::e('reservation.payment') ?></dt>

            <dd><?= Ui::paymentBadge((string) $r['payment_status']) ?><?php if (!empty($r['payment_method'])): ?> / <?= Lang::e('pay.' . $r['payment_method']) ?><?php endif; ?></dd>

            <dt><?= Lang::e('reservation.days') ?></dt>

            <dd><?= (int) $r['total_days'] ?></dd>

            <dt><?= Lang::e('reservation.total') ?></dt>

            <dd class="mono"><?= Formatter::moneyWithBrl((float) $r['final_amount']) ?></dd>

        </dl>

        <p class="muted"><?= nl2br(htmlspecialchars((string) ($r['notes'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>

    </div>

</div>

<?php if (!empty($inspections)): ?>
<div class="card mt">
    <h2 class="card-title"><?= Lang::e('reservation.inspection_history') ?></h2>
    <div class="table-wrap">
        <table class="table table--responsive">
            <thead><tr><th><?= Lang::e('reservation.inspection_kind') ?></th><th><?= Lang::e('reservation.mileage') ?></th><th><?= Lang::e('reservation.fuel_level') ?></th><th><?= Lang::e('reservation.extra_charges') ?></th><th><?= Lang::e('reservation.damage_notes') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($inspections as $insp): ?>
                <tr>
                    <td data-label="<?= Lang::e('reservation.inspection_kind') ?>"><?= Lang::e('reservation.inspection_' . ($insp['kind'] ?? 'pickup')) ?></td>
                    <td data-label="<?= Lang::e('reservation.mileage') ?>" class="mono"><?= (int) ($insp['mileage'] ?? 0) ?></td>
                    <td data-label="<?= Lang::e('reservation.fuel_level') ?>"><?= Lang::e('fuel.' . ($insp['fuel_level'] ?? 'full')) ?></td>
                    <td data-label="<?= Lang::e('reservation.extra_charges') ?>" class="mono"><?= Formatter::moneyWithBrl((float) ($insp['extra_charges'] ?? 0)) ?></td>
                    <td data-label="<?= Lang::e('reservation.damage_notes') ?>"><?= htmlspecialchars((string) ($insp['damage_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?php if (!empty($insp['photo_path'])): ?><a href="<?= htmlspecialchars(InspectionUpload::url((int) $r['id'], (string) $insp['photo_path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('actions.view') ?></a><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$fuelLevels = ['empty','quarter','half','three_quarter','full'];
$canCheckIn = in_array($r['status'], ['pending','confirmed'], true);
$canCheckOut = ($r['status'] ?? '') === 'active';
?>
<?php if ($canCheckIn || $canCheckOut): ?>
<div class="grid two mt">
    <?php if ($canCheckIn): ?>
    <div class="card">
        <h2 class="card-title"><?= Lang::e('reservation.checkin') ?></h2>
        <form method="post" action="<?= Router::url('/reservations/' . (int) $r['id'] . '/checkin') ?>" class="form-stack" id="checkinForm"
              enctype="multipart/form-data"
              data-confirm="<?= htmlspecialchars(Lang::get('reservation.checkin_confirm'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::field() ?>
            <div class="field">
                <label class="label" for="pickup_mileage"><?= Lang::e('reservation.mileage') ?></label>
                <input class="input" type="number" name="pickup_mileage" id="pickup_mileage" min="0" step="1" required inputmode="numeric">
            </div>
            <div class="field">
                <label class="label" for="fuel_level_pickup"><?= Lang::e('reservation.fuel_level') ?></label>
                <select class="input" name="fuel_level_pickup" id="fuel_level_pickup">
                    <?php foreach ($fuelLevels as $f): ?>
                        <option value="<?= $f ?>"><?= Lang::e('fuel.' . $f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label" for="damage_notes_pickup"><?= Lang::e('reservation.damage_notes') ?></label>
                <textarea class="input" name="damage_notes_pickup" id="damage_notes_pickup" rows="2"></textarea>
            </div>
            <div class="field">
                <label class="label" for="photo_pickup"><?= Lang::e('reservation.inspection_photo') ?></label>
                <input class="input" type="file" name="photo_pickup" id="photo_pickup" accept="image/jpeg,image/png,image/webp">
            </div>
            <button class="btn btn-primary" type="submit"><?= Lang::e('reservation.checkin') ?></button>
        </form>
    </div>
    <?php endif; ?>
    <?php if ($canCheckOut): ?>
    <div class="card">
        <h2 class="card-title"><?= Lang::e('reservation.checkout') ?></h2>
        <form method="post" action="<?= Router::url('/reservations/' . (int) $r['id'] . '/checkout') ?>" class="form-stack" id="checkoutForm"
              enctype="multipart/form-data"
              data-confirm="<?= htmlspecialchars(Lang::get('reservation.checkout_confirm'), ENT_QUOTES, 'UTF-8') ?>"
              data-min-mileage="<?= (int) ($r['pickup_mileage'] ?? 0) ?>"
              data-mileage-error="<?= htmlspecialchars(Lang::get('reservation.mileage_min_return'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::field() ?>
            <div class="field">
                <label class="label" for="return_mileage"><?= Lang::e('reservation.mileage') ?></label>
                <input class="input" type="number" name="return_mileage" id="return_mileage" min="<?= (int) ($r['pickup_mileage'] ?? 0) ?>" step="1" required inputmode="numeric">
                <?php if (!empty($r['pickup_mileage'])): ?>
                    <p class="muted form-hint"><?= Lang::e('reservation.mileage_pickup_ref', ['km' => (int) $r['pickup_mileage']]) ?></p>
                <?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="fuel_level_return"><?= Lang::e('reservation.fuel_level') ?></label>
                <select class="input" name="fuel_level_return" id="fuel_level_return">
                    <?php foreach ($fuelLevels as $f): ?>
                        <option value="<?= $f ?>"><?= Lang::e('fuel.' . $f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label" for="extra_charges"><?= Lang::e('reservation.extra_charges') ?></label>
                <input class="input mono" type="number" name="extra_charges" id="extra_charges" min="0" step="0.01" value="0">
            </div>
            <div class="field">
                <label class="label" for="damage_notes_return"><?= Lang::e('reservation.damage_notes') ?></label>
                <textarea class="input" name="damage_notes_return" id="damage_notes_return" rows="2"></textarea>
            </div>
            <div class="field">
                <label class="label" for="photo_return"><?= Lang::e('reservation.inspection_photo') ?></label>
                <input class="input" type="file" name="photo_return" id="photo_return" accept="image/jpeg,image/png,image/webp">
            </div>
            <p id="checkout_mileage_error" class="form-error hidden" role="alert"></p>
            <button class="btn btn-primary" type="submit"><?= Lang::e('reservation.checkout') ?></button>
        </form>
    </div>
    <?php endif; ?>
</div>
<script>
(function () {
  document.getElementById('checkinForm')?.addEventListener('submit', function (ev) {
    var msg = this.dataset.confirm;
    if (msg && !window.confirm(msg)) ev.preventDefault();
  });
  var co = document.getElementById('checkoutForm');
  if (co) {
    co.addEventListener('submit', function (ev) {
      var msg = this.dataset.confirm;
      if (msg && !window.confirm(msg)) {
        ev.preventDefault();
        return;
      }
      var min = parseInt(this.dataset.minMileage || '0', 10) || 0;
      var val = parseInt(document.getElementById('return_mileage')?.value || '0', 10) || 0;
      var errEl = document.getElementById('checkout_mileage_error');
      if (min > 0 && val < min) {
        ev.preventDefault();
        if (errEl) {
          errEl.textContent = this.dataset.mileageError || '';
          errEl.classList.remove('hidden');
        }
      }
    });
  }
})();
</script>
<?php endif; ?>

<?php if (!empty($r['actual_pickup_at']) || !empty($r['actual_return_at'])): ?>
<div class="card mt">
    <dl class="dl">
        <?php if (!empty($r['actual_pickup_at'])): ?>
        <dt><?= Lang::e('reservation.actual_pickup') ?></dt>
        <dd class="mono"><?= htmlspecialchars((string) $r['actual_pickup_at'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($r['pickup_mileage'])): ?> — <?= (int) $r['pickup_mileage'] ?> mi<?php endif; ?></dd>
        <?php endif; ?>
        <?php if (!empty($r['actual_return_at'])): ?>
        <dt><?= Lang::e('reservation.actual_return') ?></dt>
        <dd class="mono"><?= htmlspecialchars((string) $r['actual_return_at'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($r['return_mileage'])): ?> — <?= (int) $r['return_mileage'] ?> mi<?php endif; ?></dd>
        <?php endif; ?>
    </dl>
</div>
<?php endif; ?>

