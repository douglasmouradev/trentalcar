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

    /** @return array<int, int> */
    public static function partnerCarIds(): array
    {
        $ids = $_SESSION['user']['car_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_map('intval', $ids));
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
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
        ];
        if (!empty($_SESSION['lang'])) {
            return;
        }
        $_SESSION['lang'] = $user['lang_pref'] ?? 'pt-BR';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function ensureActive(): void
    {
        if (!self::check()) {
            return;
        }
        $id = self::id();
        if ($id === null) {
            return;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, role, lang_pref, is_active FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || !(int) ($row['is_active'] ?? 0)) {
            self::logout();
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $carIds = ($row['role'] ?? '') === 'partner' ? UserCar::carIdsForUser((int) $row['id']) : [];
        $_SESSION['user'] = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'role' => (string) $row['role'],
            'lang_pref' => (string) ($row['lang_pref'] ?? 'pt-BR'),
            'car_ids' => $carIds,
        ];
    }

    public static function refreshUserFromDb(): void
    {
        self::ensureActive();
    }

    public static function partnerMayViewCar(int $carId): bool
    {
        if (!self::isPartner()) {
            return true;
        }
        return in_array($carId, self::partnerCarIds(), true);
    }

    public static function recordPrivacyConsent(int $userId): void
    {
        try {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
            $stmt = Database::pdo()->prepare(
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
