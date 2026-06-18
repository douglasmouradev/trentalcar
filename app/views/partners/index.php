<?php declare(strict_types=1); /** @var array<int,array<string,mixed>> $partners */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.partners') ?></h1>
    <a class="btn btn-primary" href="<?= Router::url('/partners/create') ?>"><?= Lang::e('partner.create') ?></a>
</div>
<?php if ($partners === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.partners.title',
        'leadKey' => 'empty.partners.lead',
        'ctaUrl' => Router::url('/partners/create'),
        'ctaKey' => 'empty.partners.cta',
    ]); ?>
<?php else: ?>
<div class="table-wrap card">
    <table class="table table--responsive">
        <thead>
        <tr>
            <th><?= Lang::e('customer.name') ?></th>
            <th><?= Lang::e('auth.email') ?></th>
            <th><?= Lang::e('partner.vehicles') ?></th>
            <th><?= Lang::e('location.active') ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($partners as $p): ?>
            <tr>
                <td data-label="<?= Lang::e('customer.name') ?>"><?= htmlspecialchars((string) $p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('auth.email') ?>" class="mono"><?= htmlspecialchars((string) $p['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('partner.vehicles') ?>"><?= (int) ($p['car_count'] ?? 0) ?></td>
                <td data-label="<?= Lang::e('location.active') ?>"><?= Ui::activeBadge((bool) (int) $p['is_active']) ?></td>
                <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/partners/' . (int) $p['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a></td>
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
