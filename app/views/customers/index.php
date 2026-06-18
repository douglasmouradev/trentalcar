<?php declare(strict_types=1); /** @var array<int,array<string,mixed>> $customers */ /** @var array<string,string> $filters */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.customers') ?></h1>
    <a class="btn btn-primary" href="<?= Router::url('/customers/create') ?>"><?= Lang::e('customer.create') ?></a>
</div>
<form class="filters card" method="get">
    <div class="filters-row">
        <input class="input" type="search" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= Lang::e('actions.filter') ?>">
        <select class="input" name="type">
            <option value=""><?= Lang::e('customer.type') ?></option>
            <option value="individual" <?= ($filters['type'] ?? '') === 'individual' ? 'selected' : '' ?>><?= Lang::e('customer.individual') ?></option>
            <option value="company" <?= ($filters['type'] ?? '') === 'company' ? 'selected' : '' ?>><?= Lang::e('customer.company') ?></option>
        </select>
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </div>
</form>
<?php if ($customers === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.customers.title',
        'leadKey' => 'empty.customers.lead',
        'ctaUrl' => Router::url('/customers/create'),
        'ctaKey' => 'empty.customers.cta',
    ]); ?>
<?php else: ?>
<div class="table-wrap card mt table--responsive">
    <table class="table">
        <thead><tr><th><?= Lang::e('customer.name') ?></th><th><?= Lang::e('customer.document') ?></th><th><?= Lang::e('customer.phone') ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td data-label="<?= Lang::e('customer.name') ?>"><?= htmlspecialchars($c['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('customer.document') ?>" class="mono"><?= htmlspecialchars(Formatter::document((string) $c['document']), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('customer.phone') ?>"><?= htmlspecialchars(Formatter::phone((string) $c['phone']), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="">
                    <a class="btn btn-sm btn-secondary" href="<?= Router::url('/customers/' . (int) $c['id']) ?>"><?= Lang::e('actions.view') ?></a>
                    <a class="btn btn-sm btn-ghost" href="<?= Router::url('/customers/' . (int) $c['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>
                </td>
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
