<?php

declare(strict_types=1);

final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url(Auth::isPartner() ? '/cars' : '/dashboard'));
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
        if (LoginRateLimiter::tooManyAttempts()) {
            Flash::error(Lang::get('auth.rate_limited'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $user = User::findByEmail($email);
        if (!$user || !(int) $user['is_active'] || !password_verify($pass, $user['password_hash'])) {
            LoginRateLimiter::hit();
            Flash::error(Lang::get('auth.invalid'));
            header('Location: ' . Router::url('/login'));
            exit;
        }
        LoginRateLimiter::clear();
        Auth::login($user);
        Auth::recordPrivacyConsent((int) $user['id']);
        Flash::success(Lang::get('auth.welcome'));
        $dest = Auth::isPartner() ? '/cars' : '/dashboard';
        header('Location: ' . Router::url($dest));
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

    public function forgotSend(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        $user = $email !== '' ? User::findByEmail($email) : null;
        if ($user && (int) $user['is_active']) {
            try {
                $token = PasswordReset::createForUser((int) $user['id']);
                $link = Router::url('/reset-password') . '?token=' . urlencode($token);
                $body = Lang::get('auth.reset_email_body', [
                    'name' => (string) $user['name'],
                    'link' => $link,
                    'minutes' => '60',
                ]);
                Mail::send($email, Lang::get('auth.reset_email_subject'), $body);
            } catch (Throwable $e) {
                AppError::log($e);
            }
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
        $token = trim((string) ($_GET['token'] ?? ''));
        if ($token === '' || PasswordReset::findValidUserId($token) === null) {
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
        $token = trim((string) ($_POST['token'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password_confirm'] ?? '');
        if ($pass !== $pass2) {
            Flash::error(Lang::get('auth.reset_mismatch'));
            header('Location: ' . Router::url('/reset-password') . '?token=' . urlencode($token));
            exit;
        }
        $err = PasswordPolicy::validate($pass);
        if ($err !== null) {
            Flash::error(Lang::get('user.password_' . $err));
            header('Location: ' . Router::url('/reset-password') . '?token=' . urlencode($token));
            exit;
        }
        $userId = PasswordReset::consume($token);
        if ($userId === null) {
            Flash::error(Lang::get('auth.reset_invalid'));
            header('Location: ' . Router::url('/forgot-password'));
            exit;
        }
        User::updatePassword($userId, $pass);
        Flash::success(Lang::get('auth.reset_ok'));
        header('Location: ' . Router::url('/login'));
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }
        Auth::logout();
        header('Location: ' . Router::url('/login'));
        exit;
    }

}
