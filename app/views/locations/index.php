<?php declare(strict_types=1); /** @var array<int,array<string,mixed>> $locations */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.locations') ?></h1>
    <a class="btn btn-primary" href="<?= Router::url('/locations/create') ?>"><?= Lang::e('location.create') ?></a>
</div>
<?php if ($locations === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.locations.title',
        'leadKey' => 'empty.locations.lead',
        'ctaUrl' => Router::url('/locations/create'),
        'ctaKey' => 'location.create',
    ]); ?>
<?php else: ?>
<div class="table-wrap card table--responsive">
    <table class="table">
        <thead><tr><th><?= Lang::e('location.name') ?></th><th><?= Lang::e('location.city') ?></th><th><?= Lang::e('location.active') ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($locations as $l): ?>
            <tr>
                <td data-label="<?= Lang::e('location.name') ?>"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('location.city') ?>"><?= htmlspecialchars($l['city'] . '/' . $l['state'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('location.active') ?>"><?= Ui::activeBadge((bool) (int) $l['is_active']) ?></td>
                <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/locations/' . (int) $l['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
