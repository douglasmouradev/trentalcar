<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $monthly */
/** @var array<int,array<string,mixed>> $fleet */
/** @var string $from */
/** @var string $to */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.reports') ?></h1>
</div>
<div class="filters card">
    <form class="filters-row" method="get">
        <label class="visually-hidden" for="reports-from"><?= Lang::e('reservation.pickup') ?></label>
        <input class="input" id="reports-from" type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>">
        <label class="visually-hidden" for="reports-to"><?= Lang::e('reservation.return') ?></label>
        <input class="input" id="reports-to" type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn btn-secondary" type="submit"><?= Lang::e('actions.filter') ?></button>
    </form>
    <form method="post" action="<?= htmlspecialchars(Router::url('/reports/export'), ENT_QUOTES, 'UTF-8') ?>" class="filters-row mt inline-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn btn-secondary" type="submit"><?= Lang::e('reports.export_csv') ?></button>
    </form>
</div>
<div class="grid two mt">
    <div class="card">
        <h2 class="card-title"><?= Lang::e('nav.reports') ?> (<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>)</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th><?= Lang::e('reports.month') ?></th><th><?= Lang::e('reports.reservations_count') ?></th><th><?= Lang::e('reports.total') ?></th></tr></thead>
                <tbody>
                <?php foreach ($monthly as $row): ?>
                    <tr>
                        <td class="mono"><?= htmlspecialchars((string) $row['ym'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $row['cnt'] ?></td>
                        <td class="mono">R$ <?= number_format((float) $row['total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($monthly === []): ?><tr><td colspan="3" class="muted"><?= Lang::e('table.empty') ?></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2 class="card-title"><?= Lang::e('nav.cars') ?> (<?= Lang::e('car.status') ?>)</h2>
        <ul class="list-plain">
            <?php foreach ($fleet as $row): ?>
                <li><span class="mono"><?= Ui::carStatusBadge((string) $row['status']) ?></span> — <?= (int) $row['c'] ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
