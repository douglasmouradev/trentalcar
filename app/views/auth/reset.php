<?php declare(strict_types=1); /** @var string $token */ ?>
<p class="auth-lead"><?= Lang::e('auth.reset_lead') ?></p>
<form method="post" action="<?= Router::url('/reset-password') ?>" class="form-stack auth-form">
    <?= Csrf::field() ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
    <div class="auth-field">
        <label class="auth-label" for="reset-password"><?= Lang::e('auth.new_password') ?></label>
        <input class="input auth-input-field" id="reset-password" type="password" name="password" required autocomplete="new-password">
    </div>
    <div class="auth-field">
        <label class="auth-label" for="reset-password2"><?= Lang::e('auth.new_password_confirm') ?></label>
        <input class="input auth-input-field" id="reset-password2" type="password" name="password_confirm" required autocomplete="new-password">
    </div>
    <button class="btn btn-primary btn-block auth-submit" type="submit"><?= Lang::e('auth.reset_submit') ?></button>
</form>
