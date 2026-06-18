<?php declare(strict_types=1); ?>
<p class="auth-lead"><?= Lang::e('auth.login_subtitle') ?></p>
<form method="post" action="<?= Router::url('/login') ?>" class="form-stack auth-form">
    <?= Csrf::field() ?>
    <div class="auth-field">
        <label class="auth-label" for="login-email"><?= Lang::e('auth.email') ?></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
            <input class="input auth-input-field" id="login-email" type="email" name="email" required autocomplete="username" placeholder="<?= Lang::e('auth.placeholder_email') ?>">
        </div>
    </div>
    <div class="auth-field">
        <label class="auth-label" for="login-password"><?= Lang::e('auth.password') ?></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input class="input auth-input-field" id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="<?= Lang::e('auth.placeholder_password') ?>">
            <button type="button" class="auth-password-toggle" id="togglePassword" aria-label="<?= Lang::e('a11y.show_password') ?>" aria-pressed="false" data-show-label="<?= Lang::e('a11y.show_password') ?>" data-hide-label="<?= Lang::e('a11y.hide_password') ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
    </div>
    <label class="auth-privacy-check">
        <input type="checkbox" name="privacy_accept" value="1" required>
        <span>
            <?= Lang::e('auth.privacy_prefix') ?>
            <a href="<?= htmlspecialchars(Router::url('/privacidade'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= Lang::e('legal.nav_privacy') ?></a><?= Lang::e('auth.privacy_suffix') ?>
        </span>
    </label>
    <button class="btn btn-primary btn-block auth-submit" type="submit"><?= Lang::e('auth.submit') ?></button>
</form>
<p class="auth-back-site">
    <a class="btn btn-secondary btn-block" href="<?= htmlspecialchars(Router::url('/'), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('auth.back_to_site') ?></a>
</p>
<p class="muted auth-foot"><a href="<?= Router::url('/forgot-password') ?>"><?= Lang::e('auth.forgot_link') ?></a></p>
