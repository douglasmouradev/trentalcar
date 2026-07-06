<?php

declare(strict_types=1);

final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url(Auth::isPartner() ? '/partner/profile' : '/dashboard'));
            exit;
        }
        View::render('auth/login', ['title' => Lang::get('auth.login_title')], 'auth');
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (empty($_POST['privacy_accept'])) {
            Flash::error(Lang::get('error.privacy_required'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        if (DemoGuard::loginBlocked($email)) {
            Flash::error(Lang::get('auth.demo_blocked'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        if (LoginRateLimiter::tooManyAttempts($email)) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        $user = User::findByEmail($email);
        if (!$user || !(int) $user['is_active'] || !password_verify($pass, $user['password_hash'])) {
            LoginRateLimiter::hit($email);
            Flash::error(Lang::get('auth.invalid'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        LoginRateLimiter::clear($email);
        if (User::hasTotpSecret((int) $user['id'])) {
            PendingTwoFactor::begin((int) $user['id']);
            header('Location: ' . Router::url('/login/2fa'));
            exit;
        }
        Auth::login($user);
        self::logPrivacyConsent((int) $user['id']);
        if (Auth::mustChangePassword()) {
            Flash::error(Lang::get('auth.change_password_required'));
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        Flash::success(Lang::get('auth.welcome'));
        $dest = Auth::isPartner() ? '/partner/profile' : '/dashboard';
        header('Location: ' . Router::url($dest));
        exit;
    }

    public function twoFactorForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url(Auth::isPartner() ? '/partner/profile' : '/dashboard'));
            exit;
        }
        if (!PendingTwoFactor::isActive()) {
            PendingTwoFactor::clear();
            header('Location: ' . Router::url('/login'));
            exit;
        }
        View::render('auth/two_factor', ['title' => Lang::get('auth.2fa_title')], 'auth');
    }

    public function twoFactorVerify(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/login/2fa'));
            exit;
        }
        if (!PendingTwoFactor::isActive()) {
            PendingTwoFactor::clear();
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $uid = PendingTwoFactor::userId();
        $user = User::find($uid);
        $secret = User::getTotpSecret($uid);
        if (!$user || !(int) $user['is_active'] || $secret === null) {
            PendingTwoFactor::clear();
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $code = (string) ($_POST['code'] ?? '');
        $recovery = trim((string) ($_POST['recovery_code'] ?? ''));
        if (TotpRateLimiter::tooManyAttempts('login')) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/login/2fa'));
            exit;
        }
        $verified = false;
        if ($recovery !== '') {
            $verified = TotpRecovery::verifyAndConsume($uid, $recovery);
        } elseif ($code !== '' && Totp::verify($secret, $code)) {
            $verified = true;
        }
        if (!$verified) {
            TotpRateLimiter::hit('login');
            Flash::error(Lang::get('auth.2fa_invalid'));
            header('Location: ' . Router::url('/login/2fa'));
            exit;
        }
        TotpRateLimiter::clear('login');
        PendingTwoFactor::clear();
        Auth::login($user);
        self::logPrivacyConsent($uid);
        if (Auth::mustChangePassword()) {
            Flash::error(Lang::get('auth.change_password_required'));
            header('Location: ' . Router::url('/account/password'));
            exit;
        }
        Flash::success(Lang::get('auth.welcome'));
        $dest = Auth::isPartner() ? '/partner/profile' : '/dashboard';
        header('Location: ' . Router::url($dest));
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        Auth::logout();
        header('Location: ' . Router::url('/login'));
        exit;
    }

    public function forgotForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }
        View::render('auth/forgot', ['title' => Lang::get('auth.forgot_title')], 'auth');
    }

    public function forgotSubmit(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        if (PublicRateLimiter::forgotBlocked($email)) {
            Flash::error(Lang::get('auth.forgot_rate_limit'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        PublicRateLimiter::hitForgot($email);
        $user = User::findByEmail($email);
        if ($user && (int) $user['is_active']) {
            $token = PasswordReset::create((int) $user['id']);
            $link = Router::url('/reset-password?token=' . urlencode($token));
            Mail::queue($email, Lang::get('auth.reset_mail_subject'), Lang::get('auth.reset_mail_body', ['link' => $link]));
        }
        Flash::success(Lang::get('auth.forgot_sent'));
        header('Location: ' . Router::url('/login'));
        exit;
    }

    public function resetForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }
        $queryToken = trim((string) ($_GET['token'] ?? ''));
        if ($queryToken !== '') {
            if (PasswordReset::findValidUserId($queryToken) === null) {
                unset($_SESSION['password_reset_token']);
                Flash::error(Lang::get('auth.reset_invalid'));
                header('Location: ' . Router::url('/forgot-password'));
                exit;
            }
            $_SESSION['password_reset_token'] = $queryToken;
            header('Location: ' . Router::url('/reset-password'));
            exit;
        }
        $token = trim((string) ($_SESSION['password_reset_token'] ?? ''));
        if ($token === '' || PasswordReset::findValidUserId($token) === null) {
            unset($_SESSION['password_reset_token']);
            Flash::error(Lang::get('auth.reset_invalid'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        View::render('auth/reset', ['title' => Lang::get('auth.reset_title'), 'token' => $token], 'auth');
    }

    public function resetSubmit(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        if (PublicRateLimiter::resetBlocked()) {
            Flash::error(Lang::get('auth.reset_rate_limit'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        PublicRateLimiter::hitReset();
        $token = trim((string) ($_POST['token'] ?? ''));
        $uid = PasswordReset::findValidUserId($token);
        if ($uid === null) {
            Flash::error(Lang::get('auth.reset_invalid'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        if ($pass !== $confirm || PasswordPolicy::validate($pass) !== null) {
            $_SESSION['password_reset_token'] = $token;
            Flash::error(Lang::get('auth.password_mismatch'));
            header('Location: ' . Router::url('/reset-password'));
            exit;
        }
        User::updatePassword($uid, $pass);
        PasswordReset::consume($token);
        unset($_SESSION['password_reset_token']);
        Flash::success(Lang::get('auth.password_changed'));
        header('Location: ' . Router::url('/login'));
        exit;
    }

    private static function logPrivacyConsent(int $userId): void
    {
        try {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
            $stmt = Database::prepare(
                'INSERT INTO privacy_login_consent (user_id, ip_hash, user_agent_hash, created_at) VALUES (?, ?, ?, NOW())'
            );
            $stmt->execute([
                $userId,
                hash('sha256', $ip),
                hash('sha256', $ua),
            ]);
        } catch (Throwable) {
            /* Tabela pode ainda não existir — executar migration 003 */
        }
    }
}
