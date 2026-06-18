<?php declare(strict_types=1);
/** @var array<string,mixed> $lead */
/** @var string $whatsappUrl */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('leads.detail') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= Router::url('/leads') ?>"><?= Lang::e('actions.back') ?></a>
        <a class="btn btn-secondary" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.cta_wa') ?></a>
        <?php if (Auth::isOwner() && ($lead['status'] ?? '') !== 'converted'): ?>
        <form method="post" action="<?= Router::url('/leads/' . (int) $lead['id'] . '/convert') ?>" class="inline-form">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-primary"><?= Lang::e('leads.convert') ?></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<div class="grid two mt">
    <div class="card">
        <h2 class="card-title"><?= Lang::e('leads.contact') ?></h2>
        <dl class="dl">
            <dt><?= Lang::e('customer.name') ?></dt><dd><?= htmlspecialchars((string) $lead['full_name'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('auth.email') ?></dt><dd class="mono"><?= htmlspecialchars((string) $lead['email'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('customer.phone') ?></dt><dd><?= htmlspecialchars((string) $lead['phone'], ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </div>
    <div class="card">
        <h2 class="card-title"><?= Lang::e('landing.form_title') ?></h2>
        <dl class="dl">
            <dt><?= Lang::e('landing.form_local_label') ?></dt><dd><?= htmlspecialchars((string) $lead['local'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('landing.form_return_local_label') ?></dt><dd><?= htmlspecialchars((string) ($lead['local_devolucao'] ?? $lead['local']), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('landing.form_pickup') ?></dt><dd class="mono"><?= htmlspecialchars((string) $lead['inicio'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt><?= Lang::e('landing.form_return') ?></dt><dd class="mono"><?= htmlspecialchars((string) $lead['fim'], ENT_QUOTES, 'UTF-8') ?></dd>
            <?php if (!empty($lead['car_brand'])): ?>
            <dt><?= Lang::e('reservation.car') ?></dt><dd><?= htmlspecialchars((string) $lead['car_brand'] . ' ' . (string) ($lead['car_model'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <span class="mono muted"><?= htmlspecialchars((string) ($lead['car_plate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>
<div class="card mt">
    <form method="post" action="<?= Router::url('/leads/' . (int) $lead['id'] . '/update') ?>" class="form-stack">
        <?= Csrf::field() ?>
        <div class="grid two">
            <div class="field">
                <label class="label" for="status"><?= Lang::e('reservation.status') ?></label>
                <select class="input" name="status" id="status">
                    <?php
                    $statusOptions = Auth::isOwner()
                        ? ['new','contacted','converted','discarded']
                        : ['contacted','discarded'];
                    foreach ($statusOptions as $s):
                    ?>
                        <option value="<?= $s ?>" <?= ($lead['status'] ?? '') === $s ? 'selected' : '' ?>><?= Lang::e('leads.status_' . $s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label" for="notes"><?= Lang::e('reservation.notes') ?></label>
                <textarea class="input" name="notes" id="notes" rows="3"><?= htmlspecialchars((string) ($lead['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
    </form>
</div>
