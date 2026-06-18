<?php declare(strict_types=1); /** @var bool $required */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('auth.change_password_title') ?></h1>
</div>
<?php if ($required): ?>
    <div class="toast toast-error"><?= Lang::e('auth.change_password_required') ?></div>
<?php endif; ?>
<form class="card form-stack" method="post" action="<?= Router::url('/account/password') ?>">
    <?= Csrf::field() ?>
    <?php if (!$required): ?>
        <label class="label"><?= Lang::e('auth.password_current') ?></label>
        <input class="input" type="password" name="current_password" required autocomplete="current-password">
    <?php endif; ?>
    <label class="label"><?= Lang::e('auth.password') ?></label>
    <input class="input" type="password" name="password" required minlength="8" autocomplete="new-password">
    <p class="help-text muted"><?= Lang::e('auth.password_complexity_hint') ?></p>
    <label class="label"><?= Lang::e('auth.password_confirm') ?></label>
    <input class="input" type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
    <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
</form>
