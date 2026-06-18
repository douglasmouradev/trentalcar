<?php declare(strict_types=1);
/** @var bool $totpEnabled @var string|null $setupSecret @var string|null $setupUri */
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('account.security_title') ?></h1>
    <a class="btn btn-secondary" href="<?= Router::url('/account/password') ?>"><?= Lang::e('auth.change_password_title') ?></a>
</div>

<div class="card form-stack">
    <h2 class="card-title"><?= Lang::e('account.2fa_heading') ?></h2>
    <?php if ($totpEnabled): ?>
        <p class="muted"><?= Lang::e('account.2fa_enabled_lead') ?></p>
        <form method="post" action="<?= Router::url('/account/security/disable') ?>" class="form-stack">
            <?= Csrf::field() ?>
            <label class="label"><?= Lang::e('auth.password_current') ?></label>
            <input class="input" type="password" name="password" required>
            <button class="btn btn-danger" type="submit"><?= Lang::e('account.2fa_disable_btn') ?></button>
        </form>
    <?php elseif ($setupSecret !== null && $setupUri !== null): ?>
        <p class="muted"><?= Lang::e('account.2fa_setup_lead') ?></p>
        <p class="mono setup-secret"><?= htmlspecialchars($setupSecret, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="help-text"><a href="<?= htmlspecialchars($setupUri, ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('account.2fa_app_link') ?></a></p>
        <form method="post" action="<?= Router::url('/account/security/enable') ?>" class="form-stack">
            <?= Csrf::field() ?>
            <label class="label"><?= Lang::e('auth.password_current') ?></label>
            <input class="input" type="password" name="password" required autocomplete="current-password">
            <label class="label"><?= Lang::e('auth.2fa_code') ?></label>
            <input class="input mono" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
            <button class="btn btn-primary" type="submit"><?= Lang::e('account.2fa_confirm_btn') ?></button>
        </form>
    <?php else: ?>
        <p class="muted"><?= Lang::e('account.2fa_intro') ?></p>
        <form method="post" action="<?= Router::url('/account/security/begin') ?>" class="form-stack">
            <?= Csrf::field() ?>
            <label class="label"><?= Lang::e('auth.password_current') ?></label>
            <input class="input" type="password" name="password" required autocomplete="current-password">
            <button class="btn btn-primary" type="submit"><?= Lang::e('account.2fa_begin_btn') ?></button>
        </form>
    <?php endif; ?>
</div>
