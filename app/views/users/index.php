<?php declare(strict_types=1); /** @var array<int,array<string,mixed>> $users */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('nav.users') ?></h1>
    <a class="btn btn-primary" href="<?= Router::url('/users/create') ?>"><?= Lang::e('user.create') ?></a>
</div>
<div class="table-wrap card">
    <table class="table table--responsive">
        <thead><tr><th><?= Lang::e('customer.name') ?></th><th><?= Lang::e('auth.email') ?></th><th><?= Lang::e('user.role') ?></th><th><?= Lang::e('location.active') ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td data-label="<?= Lang::e('customer.name') ?>"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('auth.email') ?>" class="mono"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="<?= Lang::e('user.role') ?>"><?php
                    $role = (string) ($u['role'] ?? 'operator');
                    echo match ($role) {
                        'owner' => Lang::e('user.owner'),
                        'partner' => Lang::e('user.partner'),
                        default => Lang::e('user.operator'),
                    };
                ?></td>
                <td data-label="<?= Lang::e('location.active') ?>"><?= Ui::activeBadge((bool) (int) $u['is_active']) ?></td>
                <td data-label=""><a class="btn btn-sm btn-secondary" href="<?= Router::url('/users/' . (int) $u['id'] . '/edit') ?>"><?= Lang::e('actions.edit') ?></a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($users === []): ?><tr><td colspan="5" class="muted"><?= Lang::e('table.empty') ?></td></tr><?php endif; ?>
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
