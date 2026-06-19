<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $leads */
/** @var array<string,string> $filters */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('leads.title') ?></h1>
</div>
<form class="filters card" method="get">
    <div class="filters-row">
        <input class="input" type="search" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= Lang::e('actions.filter') ?>">
        <select class="input" name="status">
            <option value=""><?= Lang::e('reservation.status') ?></option>
            <?php foreach (['new','contacted','converted','discarded'] as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= Lang::e('leads.status_' . $s) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </div>
</form>
<?php if ($leads === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.leads.title',
        'leadKey' => 'empty.leads.lead',
        'ctaUrl' => Router::url('/'),
        'ctaKey' => 'empty.leads.cta',
    ]); ?>
<?php else: ?>
<div class="table-wrap card mt table--responsive">
    <table class="table">
        <thead>
        <tr>
            <th><?= Lang::e('leads.contact') ?></th>
            <th><?= Lang::e('landing.form_local_label') ?></th>
            <th><?= Lang::e('landing.form_pickup') ?></th>
            <th><?= Lang::e('reservation.status') ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($leads as $l): ?>
            <tr>
                <td data-label="<?= Lang::e('leads.contact') ?>">
                    <strong><?= htmlspecialchars((string) $l['full_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                    <span class="mono muted"><?= htmlspecialchars((string) $l['email'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td data-label="<?= Lang::e('landing.form_local_label') ?>"><?= htmlspecialchars((string) $l['local'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('landing.form_pickup') ?>" class="mono"><?= htmlspecialchars((string) $l['inicio'] . ' → ' . (string) $l['fim'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('reservation.status') ?>"><?= Lang::e('leads.status_' . ($l['status'] ?? 'new')) ?></td>
                <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/leads/' . (int) $l['id']) ?>"><?= Lang::e('actions.view') ?></a></td>
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
