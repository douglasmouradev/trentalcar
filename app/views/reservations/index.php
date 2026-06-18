<?php declare(strict_types=1); /** @var array<int,array<string,mixed>> $reservations */ /** @var array<string,string> $filters */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.reservations') ?></h1>
    <a class="btn btn-primary" href="<?= Router::url('/reservations/create') ?>"><?= Lang::e('reservation.create') ?></a>
</div>
<form class="filters card" method="get">
    <div class="filter-presets">
        <a class="btn btn-ghost btn-sm" href="<?= Router::url('/reservations?status=active') ?>"><?= Lang::e('filters.active') ?></a>
        <a class="btn btn-ghost btn-sm" href="<?= Router::url('/reservations?payment_status=unpaid') ?>"><?= Lang::e('filters.unpaid') ?></a>
        <a class="btn btn-ghost btn-sm" href="<?= Router::url('/reservations?date_from=' . date('Y-m-d') . '&date_to=' . date('Y-m-d')) ?>"><?= Lang::e('filters.today') ?></a>
    </div>
    <div class="filters-row">
        <input class="input" type="search" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= Lang::e('reservation.code') ?>">
        <select class="input" name="status">
            <option value=""><?= Lang::e('reservation.status') ?></option>
            <?php foreach (['pending','confirmed','active','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= Lang::e('status.' . $s) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="input" name="payment_status">
            <option value=""><?= Lang::e('reservation.payment') ?></option>
            <?php foreach (['unpaid','partial','paid'] as $p): ?>
                <option value="<?= $p ?>" <?= ($filters['payment_status'] ?? '') === $p ? 'selected' : '' ?>><?= Lang::e('payment.' . $p) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input" type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input class="input" type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </div>
</form>
<?php if ($reservations === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.reservations.title',
        'leadKey' => 'empty.reservations.lead',
        'ctaUrl' => Router::url('/reservations/create'),
        'ctaKey' => 'empty.reservations.cta',
    ]); ?>
<?php else: ?>
<div class="table-wrap card mt table--responsive">
    <table class="table">
        <thead>
        <tr>
            <th><?= Lang::e('reservation.code') ?></th>
            <th><?= Lang::e('reservation.customer') ?></th>
            <th><?= Lang::e('reservation.car') ?></th>
            <th><?= Lang::e('reservation.pickup') ?></th>
            <th><?= Lang::e('reservation.return') ?></th>
            <th><?= Lang::e('reservation.status') ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td data-label="<?= Lang::e('reservation.code') ?>" class="mono"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('reservation.customer') ?>"><?= htmlspecialchars($r['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('reservation.car') ?>">
                    <span class="swatch" style="background:<?= htmlspecialchars($r['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span>
                    <?= htmlspecialchars($r['brand'] . ' ' . $r['model'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td data-label="<?= Lang::e('reservation.pickup') ?>"><?= htmlspecialchars($r['pickup_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('reservation.return') ?>"><?= htmlspecialchars($r['return_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('reservation.status') ?>"><?= Ui::statusBadge((string) $r['status']) ?></td>
                <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id']) ?>"><?= Lang::e('actions.view') ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!empty($pagination)): View::partial('partials/pagination', [
        'paginationBase' => $paginationBase,
        'listQuery' => $listQuery ?? [],
        'page' => (int) $pagination['page'],
        'totalPages' => (int) $pagination['totalPages'],
        'total' => (int) $pagination['total'],
        'perPage' => (int) $pagination['perPage'],
    ]); endif; ?>
</div>
<?php endif; ?>
