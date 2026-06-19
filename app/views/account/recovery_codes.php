<?php declare(strict_types=1);
/** @var list<string> $codes @var int $remaining */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('account.recovery_title') ?></h1>
</div>
<div class="card form-stack">
    <p class="muted"><?= Lang::e('account.recovery_lead') ?></p>
    <ul class="mono recovery-codes">
        <?php foreach ($codes as $c): ?>
            <li><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="help-text"><?= Lang::e('account.recovery_hint', ['count' => (string) $remaining]) ?></p>
    <a class="btn btn-primary" href="<?= Router::url('/account/security') ?>"><?= Lang::e('actions.back') ?></a>
</div>
