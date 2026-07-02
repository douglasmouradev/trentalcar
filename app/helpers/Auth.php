<?php

declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user']['id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function isOwner(): bool
    {
        return self::role() === 'owner';
    }

    public static function isPartner(): bool
    {
        return self::role() === 'partner';
    }

    public static function isOperator(): bool
    {
        return self::role() === 'operator';
    }

    public static function isStaff(): bool
    {
        return in_array(self::role(), ['owner', 'operator'], true);
    }

    public static function mustChangePassword(): bool
    {
        return !empty($_SESSION['user']['must_change_password']);
    }

    /** @return array<int, int> */
    public static function partnerCarIds(): array
    {
        $ids = $_SESSION['user']['car_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_map('intval', $ids));
    }

    /** @param array<string, mixed> $user */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        self::setSessionUser($user);
        $_SESSION['_last_activity'] = time();
        Csrf::regenerate();
    }

    /** Revalida sessão contra a BD e expira por inactividade. */
    public static function ensureValidSession(): bool
    {
        if (!self::check()) {
            return false;
        }

        $lifetime = (int) (Config::app()['session_lifetime'] ?? 480) * 60;
        $now = time();
        $last = (int) ($_SESSION['_last_activity'] ?? $now);
        if ($now - $last > $lifetime) {
            self::logout();
            return false;
        }
        $_SESSION['_last_activity'] = $now;

        $id = self::id();
        if ($id === null) {
            self::logout();
            return false;
        }

        $row = User::findForSession($id);
        if ($row === null) {
            self::logout();
            return false;
        }

        self::setSessionUser($row);
        return true;
    }

    /**
     * Atualiza dados do utilizador na sessão sem rotacionar sessão/CSRF.
     * @param array<string, mixed> $user
     */
    public static function setSessionUser(array $user): void
    {
        $carIds = [];
        if (($user['role'] ?? '') === 'partner') {
            $carIds = UserCar::carIdsForUser((int) $user['id']);
        }
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'lang_pref' => $user['lang_pref'] ?? 'pt-BR',
            'car_ids' => $carIds,
            'must_change_password' => (int) ($user['must_change_password'] ?? 0),
        ];
        if (empty($_SESSION['lang'])) {
            $_SESSION['lang'] = $user['lang_pref'] ?? 'pt-BR';
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            $name = session_name();
            if (is_string($name)) {
                setcookie($name, '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
        }
        session_destroy();
    }

    public static function refreshUserFromDb(): void
    {
        $id = self::id();
        if ($id === null) {
            return;
        }
        $row = User::findForSession($id);
        if ($row) {
            self::setSessionUser($row);
        }
    }

    public static function partnerMayViewCar(int $carId): bool
    {
        if (!self::isPartner()) {
            return true;
        }
        return in_array($carId, self::partnerCarIds(), true);
    }
}
