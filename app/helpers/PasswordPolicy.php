<?php

declare(strict_types=1);

final class PasswordPolicy
{
    public static function validate(string $password): ?string
    {
        if (strlen($password) < 10) {
            return 'short';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'lower';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'upper';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'digit';
        }
        return null;
    }
}
