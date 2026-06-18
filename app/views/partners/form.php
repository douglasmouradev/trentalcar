<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $partner */
/** @var array<int,array<string,mixed>> $allCars */
/** @var array<int,array<string,mixed>> $assignments */
$isEdit = $partner !== null;
$assignedMap = [];
foreach ($assignments as $a) {
    $assignedMap[(int) $a['car_id']] = (float) ($a['quota_percent'] ?? 100);
}
$action = $isEdit
    ? Router::url('/partners/' . (int) $partner['id'] . '/update')
    : Router::url('/partners');
?>
<div class="page-head">
    <h1 class="page-title"><?= $isEdit ? Lang::e('partner.edit') : Lang::e('partner.create') ?></h1>
    <a class="btn btn-secondary" href="<?= Router::url('/partners') ?>"><?= Lang::e('actions.back') ?></a>
</div>
<form class="card form-stack" method="post" action="<?= $action ?>">
    <?= Csrf::field() ?>
    <label class="label"><?= Lang::e('customer.name') ?></label>
    <input class="input" name="name" required value="<?= htmlspecialchars((string) ($partner['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <label class="label"><?= Lang::e('auth.email') ?></label>
    <input class="input" type="email" name="email" required value="<?= htmlspecialchars((string) ($partner['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <label class="label"><?= Lang::e('auth.password') ?></label>
    <input class="input" type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password" placeholder="<?= $isEdit ? Lang::e('user.password_keep') : '' ?>">
    <?php if ($isEdit): ?><p class="help-text"><?= Lang::e('user.password_keep') ?></p><?php endif; ?>
    <label class="label"><?= Lang::e('customer.phone') ?></label>
    <input class="input" name="phone" data-mask="phone" value="<?= htmlspecialchars((string) ($partner['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <label class="label"><?= Lang::e('user.lang_pref') ?></label>
    <select class="input" name="lang_pref">
        <option value="pt-BR" <?= (($partner['lang_pref'] ?? 'pt-BR') === 'pt-BR') ? 'selected' : '' ?>>pt-BR</option>
        <option value="en-US" <?= (($partner['lang_pref'] ?? '') === 'en-US') ? 'selected' : '' ?>>en-US</option>
    </select>
    <label class="checkbox"><input type="checkbox" name="is_active" value="1" <?= !$isEdit || (int) ($partner['is_active'] ?? 0) ? 'checked' : '' ?>> <?= Lang::e('location.active') ?></label>

    <h2 class="card-title"><?= Lang::e('partner.quota_assignments') ?></h2>
    <p class="help-text"><?= Lang::e('partner.quota_hint') ?></p>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th><?= Lang::e('car.plate') ?></th>
                <th><?= Lang::e('car.model') ?></th>
                <th><?= Lang::e('partner.quota_percent') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($allCars as $car): ?>
                <?php
                $cid = (int) $car['id'];
                $checked = isset($assignedMap[$cid]);
                $quota = $assignedMap[$cid] ?? 100;
                ?>
                <tr>
                    <td><input type="checkbox" name="car_ids[]" value="<?= $cid ?>" class="partner-car-check" data-car-id="<?= $cid ?>" <?= $checked ? 'checked' : '' ?>></td>
                    <td class="mono"><?= htmlspecialchars((string) $car['license_plate'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $car['brand'] . ' ' . (string) $car['model'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <input class="input input-sm quota-input" type="number" name="quota_percent[<?= $cid ?>]" min="0.01" max="100" step="0.01" value="<?= htmlspecialchars(number_format($quota, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" <?= $checked ? '' : 'disabled' ?>>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
</form>
<script src="<?= htmlspecialchars(Router::url('/js/partner-form.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
