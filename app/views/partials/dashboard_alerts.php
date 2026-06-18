<?php declare(strict_types=1);
/** @var array<string,mixed> $alerts */
if (($alerts ?? []) === []) {
    return;
}
$hasAny = ($alerts['overdue'] ?? []) !== []
    || ($alerts['checkins_today'] ?? []) !== []
    || ($alerts['unpaid'] ?? []) !== []
    || ($alerts['new_leads'] ?? []) !== []
    || (int) ($alerts['stale_leads_count'] ?? 0) > 0;
if (!$hasAny) {
    return;
}
?>
<section class="dashboard-alerts mt" aria-label="<?= Lang::e('dashboard.alerts_title') ?>">
    <?php if ((int) ($alerts['new_leads_count'] ?? 0) > 0): ?>
        <div class="alert-banner alert-banner--info">
            <?= Lang::e('dashboard.new_leads_count', ['count' => (int) $alerts['new_leads_count']]) ?>
            <?php if ((int) ($alerts['stale_leads_count'] ?? 0) > 0): ?>
                · <?= Lang::e('dashboard.stale_leads_count', ['count' => (int) $alerts['stale_leads_count']]) ?>
            <?php endif; ?>
            <a class="btn btn-sm btn-secondary" href="<?= Router::url('/leads') ?>"><?= Lang::e('dashboard.view_leads') ?></a>
        </div>
    <?php endif; ?>
    <div class="grid two">
        <?php if (($alerts['checkins_today'] ?? []) !== []): ?>
        <div class="card">
            <h2 class="card-title"><?= Lang::e('dashboard.alert_checkins') ?></h2>
            <ul class="alert-list">
                <?php foreach ($alerts['checkins_today'] as $row): ?>
                    <li><a href="<?= Router::url('/reservations/' . (int) $row['id']) ?>"><span class="mono"><?= htmlspecialchars((string) $row['code'], ENT_QUOTES, 'UTF-8') ?></span> — <?= htmlspecialchars((string) $row['customer_name'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (($alerts['overdue'] ?? []) !== []): ?>
        <div class="card alert-card--warn">
            <h2 class="card-title"><?= Lang::e('dashboard.alert_overdue') ?></h2>
            <ul class="alert-list">
                <?php foreach ($alerts['overdue'] as $row): ?>
                    <li><a href="<?= Router::url('/reservations/' . (int) $row['id']) ?>"><span class="mono"><?= htmlspecialchars((string) $row['code'], ENT_QUOTES, 'UTF-8') ?></span> — <?= htmlspecialchars((string) $row['return_date'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (($alerts['unpaid'] ?? []) !== []): ?>
        <div class="card">
            <h2 class="card-title"><?= Lang::e('dashboard.alert_unpaid') ?></h2>
            <ul class="alert-list">
                <?php foreach ($alerts['unpaid'] as $row): ?>
                    <li><a href="<?= Router::url('/reservations/' . (int) $row['id']) ?>"><span class="mono"><?= htmlspecialchars((string) $row['code'], ENT_QUOTES, 'UTF-8') ?></span> — <?= Formatter::money((float) $row['final_amount']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (($alerts['new_leads'] ?? []) !== []): ?>
        <div class="card">
            <h2 class="card-title"><?= Lang::e('dashboard.alert_leads') ?></h2>
            <ul class="alert-list">
                <?php foreach ($alerts['new_leads'] as $row): ?>
                    <li><a href="<?= Router::url('/leads/' . (int) $row['id']) ?>"><?= htmlspecialchars((string) $row['full_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $row['inicio'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</section>
