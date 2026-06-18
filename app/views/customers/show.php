<?php declare(strict_types=1);
/** @var array<string,mixed> $customer */
/** @var array<int,array<string,mixed>> $reservations */
?>
<div class="page-head">
    <h1 class="page-title"><?= htmlspecialchars((string) $customer['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= Router::url('/customers') ?>"><?= Lang::e('actions.back') ?></a>
        <a class="btn btn-primary" href="<?= Router::url('/customers/' . (int) $customer['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>
    </div>
</div>
<div class="card mt">
    <dl class="dl">
        <dt><?= Lang::e('customer.document') ?></dt><dd class="mono"><?= htmlspecialchars(Formatter::document((string) $customer['document']), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('auth.email') ?></dt><dd class="mono"><?= htmlspecialchars((string) ($customer['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt><?= Lang::e('customer.phone') ?></dt><dd><?= htmlspecialchars(Formatter::phone((string) $customer['phone']), ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>
</div>
<div class="card mt">
    <h2 class="card-title"><?= Lang::e('customer.history') ?></h2>
    <?php if ($reservations === []): ?>
        <p class="muted"><?= Lang::e('empty.reservations.title') ?></p>
    <?php else: ?>
    <div class="table-wrap table--responsive">
        <table class="table">
            <thead><tr><th><?= Lang::e('reservation.code') ?></th><th><?= Lang::e('reservation.car') ?></th><th><?= Lang::e('reservation.pickup') ?></th><th><?= Lang::e('reservation.status') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td data-label="<?= Lang::e('reservation.code') ?>" class="mono"><?= htmlspecialchars((string) $r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('reservation.car') ?>"><?= htmlspecialchars((string) $r['brand'] . ' ' . (string) $r['model'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('reservation.pickup') ?>" class="mono"><?= htmlspecialchars((string) $r['pickup_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="<?= Lang::e('reservation.status') ?>"><?= Ui::statusBadge((string) $r['status']) ?></td>
                    <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id']) ?>"><?= Lang::e('actions.view') ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
