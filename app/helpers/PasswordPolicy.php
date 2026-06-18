<?php

declare(strict_types=1);

final class PasswordPolicy
{
    /** Devolve mensagem de erro traduzida ou null se válida. */
    public static function validate(string $password): ?string
    {
        if (strlen($password) < 8) {
            return Lang::get('user.password_short');
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return Lang::get('auth.password_complexity');
        }
        $email = (string) (Auth::user()['email'] ?? '');
        if ($email !== '' && str_contains(strtolower($password), strtolower(explode('@', $email)[0]))) {
            return Lang::get('auth.password_like_email');
        }
        return null;
    }
}
