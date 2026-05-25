<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $reservations */
/** @var array{status:string,q:string,from:string,to:string} $filters */
$statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.reservations') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= htmlspecialchars(Router::url('/reservations/export') . '?' . http_build_query(array_filter($filters, static fn ($v) => $v !== '')), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('actions.export_csv') ?></a>
        <a class="btn btn-primary" href="<?= Router::url('/reservations/create') ?>"><?= Lang::e('reservation.create') ?></a>
    </div>
</div>
<form class="filters card" method="get" action="<?= Router::url('/reservations') ?>">
    <div class="filters-row">
        <input class="input" type="search" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= Lang::e('reservation.search_ph') ?>">
        <select class="input" name="status">
            <option value=""><?= Lang::e('reservation.all_status') ?></option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= Lang::e('status.' . $s) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input" type="date" name="from" value="<?= htmlspecialchars($filters['from'], ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= Lang::e('reservation.from') ?>">
        <input class="input" type="date" name="to" value="<?= htmlspecialchars($filters['to'], ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= Lang::e('reservation.to') ?>">
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </div>
</form>
<div class="table-wrap card">
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
                <td class="mono"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="cell-vehicle">
                        <span class="swatch" style="background:<?= htmlspecialchars($r['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span>
                        <span>
                            <span class="cell-vehicle-name"><?= htmlspecialchars($r['brand'] . ' ' . $r['model'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="cell-vehicle-plate mono"><?= htmlspecialchars($r['license_plate'], ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </div>
                </td>
                <td><?= htmlspecialchars($r['pickup_date'] . ' ' . substr((string) $r['pickup_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['return_date'] . ' ' . substr((string) $r['return_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge st-<?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('status.' . $r['status']) ?></span></td>
                <td><a class="btn btn-sm btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id']) ?>"><?= Lang::e('actions.view') ?></a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($reservations === []): ?><tr><td colspan="7" class="muted"><?= Lang::e('table.empty') ?></td></tr><?php endif; ?>
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
