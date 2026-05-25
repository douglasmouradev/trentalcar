<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $leads */
/** @var string $statusFilter */
$statuses = ['new', 'contacted', 'converted', 'archived'];
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.leads') ?></h1>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= htmlspecialchars(Router::url('/leads/export') . ($statusFilter !== '' ? '?status=' . urlencode($statusFilter) : ''), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('actions.export_csv') ?></a>
    </div>
</div>
<form class="filters card" method="get" action="<?= Router::url('/leads') ?>">
    <label class="label"><?= Lang::e('lead.status') ?></label>
    <select class="input" name="status" onchange="this.form.submit()">
        <option value=""><?= Lang::e('actions.filter') ?> — <?= Lang::e('lead.all') ?></option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= Lang::e('lead.status_' . $s) ?></option>
        <?php endforeach; ?>
    </select>
</form>
<div class="table-wrap card">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th><?= Lang::e('lead.location') ?></th>
                <th><?= Lang::e('lead.contact') ?></th>
                <th><?= Lang::e('lead.dates') ?></th>
                <th><?= Lang::e('lead.status') ?></th>
                <th><?= Lang::e('lead.received') ?></th>
                <th><?= Lang::e('table.actions') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($leads as $lead): ?>
            <tr>
                <td class="mono"><?= (int) $lead['id'] ?></td>
                <td>
                    <?= htmlspecialchars((string) $lead['location_text'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!(int) $lead['same_location']): ?>
                        <div class="muted"><?= Lang::e('lead.return') ?>: <?= htmlspecialchars((string) ($lead['return_location_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($lead['contact_name'])): ?>
                        <div><?= htmlspecialchars((string) $lead['contact_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($lead['contact_phone'])): ?>
                        <div class="mono"><?= htmlspecialchars((string) $lead['contact_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($lead['contact_email'])): ?>
                        <div class="mono"><?= htmlspecialchars((string) $lead['contact_email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </td>
                <td class="mono"><?= htmlspecialchars((string) $lead['start_date'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) $lead['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= Lang::e('lead.status_' . $lead['status']) ?></td>
                <td class="mono"><?= htmlspecialchars((string) $lead['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($lead['status'] !== 'converted'): ?>
                        <form method="post" action="<?= Router::url('/leads/' . (int) $lead['id'] . '/convert') ?>" class="inline-form">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-primary"><?= Lang::e('lead.convert') ?></button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="<?= Router::url('/leads/' . (int) $lead['id'] . '/status') ?>" class="inline-form stack-sm">
                        <?= Csrf::field() ?>
                        <select class="input input-sm" name="status">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= ((string) $lead['status'] === $s) ? 'selected' : '' ?>><?= Lang::e('lead.status_' . $s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input class="input input-sm" name="notes" placeholder="<?= Lang::e('lead.notes') ?>" value="<?= htmlspecialchars((string) ($lead['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-secondary"><?= Lang::e('actions.save') ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($leads === []): ?><tr><td colspan="7" class="muted"><?= Lang::e('table.empty') ?></td></tr><?php endif; ?>
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
