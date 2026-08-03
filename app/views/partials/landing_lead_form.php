<?php declare(strict_types=1);
/** @var callable(string): string $asset */
/** @var array<string,mixed> $leadOld */
/** @var array<int,string> $leadErrors */
/** @var array<string,mixed>|null $selectedCar */
/** @var string $formAction */
/** @var string $returnPath */
$leadOld = $leadOld ?? [];
$leadErrors = $leadErrors ?? [];
$returnPath = $returnPath ?? '/';
$carId = (int) ($leadOld['car_id'] ?? 0);
if ($selectedCar !== null) {
    $carId = (int) ($selectedCar['id'] ?? $carId);
}
$today = date('Y-m-d');
$hotelSelected = LeadPickupOptions::isHotel((string) ($leadOld['local'] ?? ''));
?>
<div class="lp-form-errors" id="lead-form-errors" role="alert" <?= $leadErrors === [] ? 'hidden' : '' ?>>
  <?php if ($leadErrors !== []): ?>
    <ul class="lp-form-errors-list">
      <?php foreach ($leadErrors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php if ($selectedCar !== null): ?>
  <p class="lp-car-selected" id="lead-car-selected" role="status">
    <?= Lang::e('landing.car_selected') ?>
    <strong><?= htmlspecialchars((string) $selectedCar['brand'] . ' ' . (string) $selectedCar['model'], ENT_QUOTES, 'UTF-8') ?></strong>
    <button type="button" class="lp-car-selected-clear" id="lead-car-clear" aria-label="<?= Lang::e('landing.car_clear') ?>">&times;</button>
  </p>
<?php endif; ?>
<form class="lp-booking" id="form-busca" method="post" action="<?= $asset($formAction) ?>"
      aria-describedby="lp-booking-hint"
      data-error-date-order="<?= htmlspecialchars(Lang::get('landing.error_date_order'), ENT_QUOTES, 'UTF-8') ?>"
      data-error-date-required="<?= htmlspecialchars(Lang::get('landing.error_date_required'), ENT_QUOTES, 'UTF-8') ?>"
      data-error-hotel="<?= htmlspecialchars(Lang::get('landing.error_hotel'), ENT_QUOTES, 'UTF-8') ?>"
      data-hotel-value="<?= htmlspecialchars(LeadPickupOptions::HOTEL, ENT_QUOTES, 'UTF-8') ?>"
      data-label-submitting="<?= htmlspecialchars(Lang::get('landing.form_submitting'), ENT_QUOTES, 'UTF-8') ?>">
  <?= Csrf::field() ?>
  <input type="hidden" name="_return" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
  <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
  <div class="lp-booking-header">
    <p class="lp-booking-title"><?= Lang::e('landing.form_title') ?></p>
    <p id="lp-booking-hint" class="lp-booking-hint"><?= Lang::e('landing.lead_hint') ?></p>
  </div>
  <input type="hidden" name="car_id" id="lead-car-id" value="<?= $carId ?>">
  <fieldset class="lp-booking-section">
    <legend class="lp-booking-section-title"><?= Lang::e('leads.contact') ?></legend>
    <div class="lp-booking-grid lp-booking-grid--contact">
      <label class="lp-field lp-field--grow">
        <span class="lp-label"><?= Lang::e('landing.form_name') ?></span>
        <input class="lp-input" type="text" name="nome" maxlength="150" autocomplete="name" required value="<?= htmlspecialchars((string) ($leadOld['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
      <label class="lp-field">
        <span class="lp-label"><?= Lang::e('landing.form_email') ?></span>
        <input class="lp-input" type="email" name="email" maxlength="180" autocomplete="email" required value="<?= htmlspecialchars((string) ($leadOld['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
      <label class="lp-field">
        <span class="lp-label"><?= Lang::e('landing.form_phone') ?></span>
        <input class="lp-input" type="tel" name="telefone" maxlength="30" autocomplete="tel" required value="<?= htmlspecialchars((string) ($leadOld['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
    </div>
  </fieldset>
  <fieldset class="lp-booking-section">
    <legend class="lp-booking-section-title"><?= Lang::e('landing.form_trip') ?></legend>
    <div class="lp-booking-grid">
      <label class="lp-field lp-field--grow">
        <span class="lp-label"><?= Lang::e('landing.form_local_label') ?></span>
        <select class="lp-input" name="local" id="lead-local" required>
          <option value="" disabled <?= ((string) ($leadOld['local'] ?? '')) === '' ? 'selected' : '' ?>><?= Lang::e('landing.form_local_ph') ?></option>
          <?php foreach (LeadPickupOptions::choices() as $opt): ?>
            <option value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($leadOld['local'] ?? '')) === $opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="lp-field">
        <span class="lp-label"><?= Lang::e('landing.form_pickup') ?></span>
        <input class="lp-input" type="date" name="inicio" required min="<?= $today ?>" value="<?= htmlspecialchars((string) ($leadOld['inicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
      <label class="lp-field">
        <span class="lp-label"><?= Lang::e('landing.form_return') ?></span>
        <input class="lp-input" type="date" name="fim" required min="<?= htmlspecialchars((string) ($leadOld['inicio'] ?? $today), ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars((string) ($leadOld['fim'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
      <div class="lp-field lp-field--btn">
        <span class="lp-label lp-label--ghost" aria-hidden="true">&nbsp;</span>
        <button type="submit" class="btn btn-search"><?= Lang::e('landing.form_submit') ?></button>
      </div>
    </div>
    <div class="lp-hotel-name<?= $hotelSelected ? ' lp-hotel-name--visible' : '' ?>" id="lp-hotel-name"<?= $hotelSelected ? '' : ' hidden' ?>>
      <label class="lp-field lp-field--grow">
        <span class="lp-label"><?= Lang::e('landing.form_hotel_label') ?></span>
        <input class="lp-input" type="text" name="hotel_nome" id="lead-hotel-nome" maxlength="120" autocomplete="organization"
               placeholder="<?= Lang::e('landing.form_hotel_ph') ?>"
               <?= $hotelSelected ? 'required' : '' ?>
               value="<?= htmlspecialchars((string) ($leadOld['hotel_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </label>
    </div>
  </fieldset>
  <?php $mesmoChecked = !isset($leadOld['mesmo_local']) || (string) ($leadOld['mesmo_local'] ?? '1') === '1'; ?>
  <label class="lp-same-return">
    <input type="checkbox" name="mesmo_local" value="1" <?= $mesmoChecked ? 'checked' : '' ?>>
    <?= Lang::e('landing.form_same_return') ?>
  </label>
  <div class="lp-return-location<?= $mesmoChecked ? '' : ' lp-return-location--visible' ?>" id="lp-return-location">
    <label class="lp-field lp-field--grow">
      <span class="lp-label"><?= Lang::e('landing.form_return_local_label') ?></span>
      <select class="lp-input" name="local_devolucao" id="lead-local-devolucao">
        <option value="" <?= ((string) ($leadOld['local_devolucao'] ?? '')) === '' ? 'selected' : '' ?>><?= Lang::e('landing.form_return_local_ph') ?></option>
        <?php foreach (LeadPickupOptions::choices() as $opt): ?>
          <option value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($leadOld['local_devolucao'] ?? '')) === $opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
</form>
