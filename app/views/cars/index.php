<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $cars */
/** @var array<string,string> $filters */
/** @var bool $canEdit */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.cars') ?></h1>
    <?php if ($canEdit): ?><a class="btn btn-primary" href="<?= Router::url('/cars/create') ?>"><?= Lang::e('car.create') ?></a><?php endif; ?>
</div>
<form class="filters card" method="get">
    <div class="filters-row">
        <input class="input" type="text" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= Lang::e('actions.filter') ?>">
        <select class="input" name="status">
            <option value=""><?= Lang::e('car.status') ?></option>
            <?php foreach (['available','rented','maintenance','inactive'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= Lang::e('car.' . $s) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="input" name="category">
            <option value=""><?= Lang::e('car.category') ?></option>
            <?php foreach (['economy','standard','suv','luxury','van','truck'] as $c): ?>
                <option value="<?= $c ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= Lang::e('category.' . $c) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </div>
</form>
<?php if ($cars === []): ?>
    <?php View::partial('partials/empty_state', [
        'titleKey' => 'empty.cars.title',
        'leadKey' => 'empty.cars.lead',
        'ctaUrl' => $canEdit ? Router::url('/cars/create') : null,
        'ctaKey' => $canEdit ? 'empty.cars.cta' : null,
    ]); ?>
<?php else: ?>
<div class="table-wrap card mt">
    <table class="table table--responsive">
        <thead>
        <tr>
            <th class="th-thumb"><?= Lang::e('car.image') ?></th>
            <th class="th-swatch" aria-label="<?= Lang::e('car.color') ?>"></th>
            <th><?= Lang::e('car.plate') ?></th>
            <th><?= Lang::e('car.model') ?></th>
            <th><?= Lang::e('car.category') ?></th>
            <th><?= Lang::e('car.daily_rate') ?></th>
            <th><?= Lang::e('car.status') ?></th>
            <th><?= Lang::e('table.actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cars as $car): ?>
            <?php
            $imgUrl = trim((string) ($car['image_url'] ?? ''));
            $carLabel = htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8');
            ?>
            <tr>
                <td class="td-thumb" data-label="<?= Lang::e('car.image') ?>">
                    <?php if ($imgUrl !== ''): ?>
                        <a href="<?= Router::url('/cars/' . (int) $car['id']) ?>" class="car-thumb-link" title="<?= $carLabel ?>">
                            <img class="car-thumb" src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" width="72" height="54" loading="lazy">
                        </a>
                    <?php else: ?>
                        <a href="<?= Router::url('/cars/' . (int) $car['id']) ?>" class="car-thumb-link car-thumb-link--empty" title="<?= Lang::e('car.image') ?> — <?= $carLabel ?>">
                            <span class="car-thumb car-thumb--empty" aria-hidden="true"></span>
                        </a>
                    <?php endif; ?>
                </td>
                <td class="td-swatch" data-label="<?= Lang::e('car.color') ?>"><span class="swatch" style="background:<?= htmlspecialchars($car['color_hex'], ENT_QUOTES, 'UTF-8') ?>"></span></td>
                <td class="mono" data-label="<?= Lang::e('car.plate') ?>"><?= htmlspecialchars($car['license_plate'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('car.model') ?>"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('car.category') ?>"><?= Ui::categoryLabel((string) $car['category']) ?></td>
                <td class="mono" data-label="<?= Lang::e('car.daily_rate') ?>"><?= Formatter::moneyWithBrl((float) $car['daily_rate']) ?></td>
                <td data-label="<?= Lang::e('car.status') ?>"><?= Ui::carStatusBadge((string) $car['status']) ?></td>
                <td class="actions" data-label="<?= Lang::e('table.actions') ?>">
                    <a class="btn btn-sm btn-secondary" href="<?= Router::url('/cars/' . (int) $car['id']) ?>"><?= Lang::e('actions.view') ?></a>
                    <?php if ($canEdit): ?>
                        <a class="btn btn-sm btn-ghost" href="<?= Router::url('/cars/' . (int) $car['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a>
                    <?php endif; ?>
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
