<?php declare(strict_types=1); ?>
<p class="auth-lead"><?= Lang::e('auth.forgot_lead') ?></p>
<form method="post" action="<?= Router::url('/forgot-password') ?>" class="form-stack auth-form">
    <?= Csrf::field() ?>
    <div class="auth-field">
        <label class="auth-label" for="forgot-email"><?= Lang::e('auth.email') ?></label>
        <input class="input auth-input-field" id="forgot-email" type="email" name="email" required autocomplete="username">
    </div>
    <button class="btn btn-primary btn-block auth-submit" type="submit"><?= Lang::e('auth.forgot_submit') ?></button>
</form>
<p class="auth-back-site"><a class="btn btn-secondary btn-block" href="<?= Router::url('/login') ?>"><?= Lang::e('actions.back') ?></a></p>
