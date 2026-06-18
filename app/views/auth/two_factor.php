<?php declare(strict_types=1); ?>
<p class="auth-lead"><?= Lang::e('auth.2fa_lead') ?></p>
<form method="post" action="<?= Router::url('/login/2fa') ?>" class="form-stack auth-form">
    <?= Csrf::field() ?>
    <div class="auth-field">
        <label class="auth-label" for="code"><?= Lang::e('auth.2fa_code') ?></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input class="input auth-input-field mono auth-otp-input" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" autofocus placeholder="000000">
        </div>
    </div>
    <button class="btn btn-primary btn-block auth-submit" type="submit"><?= Lang::e('auth.2fa_submit') ?></button>
</form>
<p class="muted auth-foot"><a href="<?= Router::url('/login') ?>"><?= Lang::e('actions.back') ?></a></p>
