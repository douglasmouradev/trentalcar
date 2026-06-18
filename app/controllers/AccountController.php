<?php

declare(strict_types=1);

final class AccountController
{
    public function passwordForm(): void
    {
        View::render('account/password', [
            'title' => Lang::get('auth.change_password_title'),
            'required' => Auth::mustChangePassword(),
        ], 'main');
    }

    public function passwordUpdate(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $current = (string) ($_POST['current_password'] ?? '');
        $policyError = PasswordPolicy::validate($pass);
        if ($policyError !== null) {
            Flash::error($policyError);
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        if ($pass !== $confirm) {
            Flash::error(Lang::get('auth.password_mismatch'));
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (PasswordRateLimiter::tooManyAttempts($uid, 'password')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        if (!Auth::mustChangePassword()) {
            if ($current === '' || !User::verifyPassword($uid, $current)) {
                PasswordRateLimiter::hit($uid, 'password');
                Flash::error(Lang::get('auth.password_current_invalid'));
                header('Location: ' . Router::url('/account/password'));
                exit;
            }
        }
        PasswordRateLimiter::clear($uid, 'password');
        User::updatePassword($uid, $pass);
        Auth::refreshUserFromDb();
        Flash::success(Lang::get('auth.password_changed'));
        $dest = Auth::isPartner() ? '/partner/profile' : '/dashboard';
        header('Location: ' . Router::url($dest));
        exit;
    }

    public function securityForm(): void
    {
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $user = User::find($uid);
        $enabled = User::hasTotpSecret($uid);
        $pendingSecret = $_SESSION['totp_setup_secret'] ?? null;
        View::render('account/security', [
            'title' => Lang::get('account.security_title'),
            'totpEnabled' => $enabled,
            'setupSecret' => is_string($pendingSecret) ? $pendingSecret : null,
            'setupUri' => is_string($pendingSecret)
                ? Totp::provisioningUri($pendingSecret, (string) ($user['email'] ?? ''), Lang::get('app.name'))
                : null,
        ], 'main');
    }

    public function enableTotp(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        if (!Schema::hasColumn('users', 'totp_secret')) {
            Flash::error(Lang::get('account.2fa_unavailable'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (PasswordRateLimiter::tooManyAttempts($uid, 'totp_enable')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass === '' || !User::verifyPassword($uid, $pass)) {
            PasswordRateLimiter::hit($uid, 'totp_enable');
            Flash::error(Lang::get('auth.password_current_invalid'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        if (TotpRateLimiter::tooManyAttempts('enable')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $secret = (string) ($_SESSION['totp_setup_secret'] ?? '');
        $code = (string) ($_POST['code'] ?? '');
        if ($secret === '' || !Totp::verify($secret, $code)) {
            TotpRateLimiter::hit('enable');
            Flash::error(Lang::get('auth.2fa_invalid'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        TotpRateLimiter::clear('enable');
        PasswordRateLimiter::clear($uid, 'totp_enable');
        User::setTotpSecret($uid, $secret);
        unset($_SESSION['totp_setup_secret']);
        Flash::success(Lang::get('account.2fa_enabled'));
        header('Location: ' . Router::url('/account/security'));
        exit;
    }

    public function disableTotp(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (PasswordRateLimiter::tooManyAttempts($uid, 'totp_disable')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass === '' || !User::verifyPassword($uid, $pass)) {
            PasswordRateLimiter::hit($uid, 'totp_disable');
            Flash::error(Lang::get('auth.password_current_invalid'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        PasswordRateLimiter::clear($uid, 'totp_disable');
        User::clearTotpSecret($uid);
        unset($_SESSION['totp_setup_secret']);
        Flash::success(Lang::get('account.2fa_disabled'));
        header('Location: ' . Router::url('/account/security'));
        exit;
    }

    public function beginTotpSetup(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        if (!Schema::hasColumn('users', 'totp_secret')) {
            Flash::error(Lang::get('account.2fa_unavailable'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (PasswordRateLimiter::tooManyAttempts($uid, 'totp_begin')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass === '' || !User::verifyPassword($uid, $pass)) {
            PasswordRateLimiter::hit($uid, 'totp_begin');
            Flash::error(Lang::get('auth.password_current_invalid'));
            header('Location: ' . Router::url('/account/security'));
            exit;
        }
        PasswordRateLimiter::clear($uid, 'totp_begin');
        $_SESSION['totp_setup_secret'] = Totp::generateSecret();
        header('Location: ' . Router::url('/account/security'));
        exit;
    }
}
