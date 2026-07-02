<?php

declare(strict_types=1);

final class User
{
    /** @return array<string, mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Utilizador activo para revalidação de sessão.
     * @return array<string, mixed>|null
     */
    public static function findForSession(int $id): ?array
    {
        $cols = 'id, name, email, role, lang_pref';
        if (Schema::hasColumn('users', 'must_change_password')) {
            $cols .= ', must_change_password';
        }
        $stmt = Database::prepare("SELECT {$cols} FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countActiveOwners(): int
    {
        $stmt = Database::query("SELECT COUNT(*) FROM users WHERE role = 'owner' AND is_active = 1");
        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, string> */
    public static function staffNotificationEmails(): array
    {
        $notify = trim((string) ($_ENV['MAIL_NOTIFY'] ?? ''));
        $emails = [];
        if ($notify !== '') {
            foreach (preg_split('/[\s,;]+/', $notify) ?: [] as $e) {
                $e = trim($e);
                if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $e;
                }
            }
        }
        if ($emails === []) {
            $rows = Database::query(
                "SELECT email FROM users WHERE role IN ('owner','operator') AND is_active = 1"
            )->fetchAll();
            foreach ($rows as $row) {
                $e = trim((string) ($row['email'] ?? ''));
                if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $e;
                }
            }
        }
        return array_values(array_unique($emails));
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        $cols = self::listColumns();
        return Database::query("SELECT {$cols} FROM users ORDER BY name")->fetchAll();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginated(int $page, int $perPage, bool $staffOnly = false): array
    {
        $where = $staffOnly ? "WHERE role IN ('owner','operator')" : '';
        $total = (int) Database::query('SELECT COUNT(*) FROM users ' . $where)->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $cols = self::listColumns();
        $stmt = Database::prepare(
            "SELECT {$cols} FROM users {$where} ORDER BY name LIMIT " . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset']
        );
        $stmt->execute();
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        if (Schema::hasColumn('users', 'must_change_password')) {
            $stmt = Database::prepare(
                'INSERT INTO users (name, email, password_hash, role, phone, is_active, must_change_password, lang_pref) VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hash,
                $data['role'],
                $data['phone'] ?? null,
                (int) ($data['is_active'] ?? 1),
                (int) ($data['must_change_password'] ?? 0),
                $data['lang_pref'] ?? 'pt-BR',
            ]);
        } else {
            $stmt = Database::prepare(
                'INSERT INTO users (name, email, password_hash, role, phone, is_active, lang_pref) VALUES (?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hash,
                $data['role'],
                $data['phone'] ?? null,
                (int) ($data['is_active'] ?? 1),
                $data['lang_pref'] ?? 'pt-BR',
            ]);
        }
        return (int) Database::pdo()->lastInsertId();
    }

    public static function emailTakenByOther(string $email, int $excludeUserId): bool
    {
        $stmt = Database::prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmt->execute([$email, $excludeUserId]);
        return (bool) $stmt->fetch();
    }

    /**
     * @param array{name:string,email:string,role:string,phone?:string|null,is_active:int,lang_pref:string,password?:string,must_change_password?:int} $data
     */
    public static function update(int $id, array $data): void
    {
        $fields = 'name = ?, email = ?, role = ?, phone = ?, is_active = ?, lang_pref = ?';
        $params = [
            $data['name'],
            $data['email'],
            $data['role'],
            $data['phone'] ?? null,
            (int) ($data['is_active'] ?? 1),
            $data['lang_pref'] ?? 'pt-BR',
        ];
        if (!empty($data['password'])) {
            $fields .= ', password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            if (Schema::hasColumn('users', 'must_change_password')) {
                $fields .= ', must_change_password = 0';
            }
        }
        $params[] = $id;
        Database::prepare('UPDATE users SET ' . $fields . ' WHERE id = ?')->execute($params);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        if (Schema::hasColumn('users', 'must_change_password')) {
            $stmt = Database::prepare(
                'UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?'
            );
            $stmt->execute([$hash, $id]);
            return;
        }
        $stmt = Database::prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public static function verifyPassword(int $id, string $password): bool
    {
        $stmt = Database::prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && password_verify($password, $hash);
    }

    public static function setTotpSecret(int $id, string $secret): void
    {
        if (!Schema::hasColumn('users', 'totp_secret')) {
            return;
        }
        $stmt = Database::prepare('UPDATE users SET totp_secret = ? WHERE id = ?');
        $stmt->execute([SecretCipher::encrypt($secret), $id]);
    }

    public static function clearTotpSecret(int $id): void
    {
        if (!Schema::hasColumn('users', 'totp_secret')) {
            return;
        }
        $stmt = Database::prepare('UPDATE users SET totp_secret = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function hasTotpSecret(int $id): bool
    {
        if (!Schema::hasColumn('users', 'totp_secret')) {
            return false;
        }
        $stmt = Database::prepare('SELECT totp_secret FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $v = $stmt->fetchColumn();
        return is_string($v) && $v !== '';
    }

    public static function getTotpSecret(int $id): ?string
    {
        if (!Schema::hasColumn('users', 'totp_secret')) {
            return null;
        }
        $stmt = Database::prepare('SELECT totp_secret FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $v = $stmt->fetchColumn();
        if (!is_string($v) || $v === '') {
            return null;
        }
        try {
            return SecretCipher::decrypt($v);
        } catch (Throwable) {
            return null;
        }
    }

    private static function listColumns(): string
    {
        $cols = 'id, name, email, role, phone, is_active, lang_pref, created_at';
        if (Schema::hasColumn('users', 'must_change_password')) {
            $cols .= ', must_change_password';
        }
        return $cols;
    }
}
