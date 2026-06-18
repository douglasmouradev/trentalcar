<?php declare(strict_types=1);
/** @var string $token */
?>
<p class="auth-lead"><?= Lang::e('auth.password_complexity_hint') ?></p>
<form method="post" action="<?= Router::url('/reset-password') ?>" class="form-stack auth-form">
    <?= Csrf::field() ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
    <div class="auth-field">
        <label class="auth-label" for="reset-password"><?= Lang::e('auth.password') ?></label>
        <input class="input auth-input-field" id="reset-password" type="password" name="password" required autocomplete="new-password">
    </div>
    <div class="auth-field">
        <label class="auth-label" for="reset-confirm"><?= Lang::e('auth.password_confirm') ?></label>
        <input class="input auth-input-field" id="reset-confirm" type="password" name="password_confirm" required autocomplete="new-password">
    </div>
    <button class="btn btn-primary btn-block auth-submit" type="submit"><?= Lang::e('auth.reset_submit') ?></button>
</form>
