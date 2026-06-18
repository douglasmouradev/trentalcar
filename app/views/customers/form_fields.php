<?php declare(strict_types=1); /** @var array<string,mixed>|null $customer */ $c = $customer ?? []; ?>
<label class="label"><?= Lang::e('customer.type') ?></label>
<select class="input" name="type">
    <option value="individual" <?= (($c['type'] ?? '') === 'individual') ? 'selected' : '' ?>><?= Lang::e('customer.individual') ?></option>
    <option value="company" <?= (($c['type'] ?? '') === 'company') ? 'selected' : '' ?>><?= Lang::e('customer.company') ?></option>
</select>
<label class="label"><?= Lang::e('customer.name') ?></label>
<input class="input" name="full_name" required value="<?= htmlspecialchars((string) ($c['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="label"><?= Lang::e('customer.document') ?></label>
<input class="input" name="document" required data-mask="document" value="<?= htmlspecialchars((string) ($c['document'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="label"><?= Lang::e('customer.email') ?></label>
<input class="input" type="email" name="email" value="<?= htmlspecialchars((string) ($c['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="label"><?= Lang::e('customer.phone') ?></label>
<input class="input" name="phone" required data-mask="phone" value="<?= htmlspecialchars((string) ($c['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="label"><?= Lang::e('location.address') ?></label>
<input class="input" name="address" value="<?= htmlspecialchars((string) ($c['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<div class="grid two">
    <div>
        <label class="label"><?= Lang::e('location.city') ?></label>
        <input class="input" name="city" value="<?= htmlspecialchars((string) ($c['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div>
        <label class="label"><?= Lang::e('location.state') ?></label>
        <input class="input" name="state" value="<?= htmlspecialchars((string) ($c['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
</div>
<label class="label"><?= Lang::e('location.zip') ?></label>
<input class="input" name="zip_code" value="<?= htmlspecialchars((string) ($c['zip_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="label"><?= Lang::e('reservation.notes') ?></label>
<textarea class="input" name="notes" rows="2"><?= htmlspecialchars((string) ($c['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

<div class="field-group">
    <label class="label" for="customer-attachment"><?= Lang::e('customer.attachment') ?></label>
    <input class="input" id="customer-attachment" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
    <?php if (!empty($c['attachment_path']) && !empty($c['id'])): ?>
        <p class="help-text">
            <a href="<?= htmlspecialchars(Router::url('/customers/' . (int) $c['id'] . '/attachment'), ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer">
                <?= Lang::e('customer.attachment_view') ?>
            </a>
        </p>
    <?php endif; ?>
</div>
