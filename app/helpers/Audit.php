<?php

declare(strict_types=1);

final class Audit
{
    /** @var list<string> */
    private const SENSITIVE_FIELDS = [
        'document',
        'phone',
        'email',
        'address',
        'full_name',
        'notes',
        'password',
        'password_hash',
        'attachment_path',
        'totp_secret',
    ];

    /**
     * @param array<string, mixed>|null $oldData
     * @param array<string, mixed>|null $newData
     */
    public static function log(
        ?int $userId,
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ipStored = is_string($ip) && $ip !== '' ? hash('sha256', $ip) : null;
        $stmt = Database::prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, old_data, new_data, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $entity,
            $entityId,
            $oldData === null ? null : json_encode(self::redact($oldData), JSON_THROW_ON_ERROR),
            $newData === null ? null : json_encode(self::redact($newData), JSON_THROW_ON_ERROR),
            $ipStored,
        ]);
    }

    /**
     * Redação forte para exportação/UI (LGPD).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function sanitize(array $data): array
    {
        $out = $data;
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (!isset($out[$field]) || !is_string($out[$field]) || $out[$field] === '') {
                continue;
            }
            $out[$field] = $field === 'attachment_path' ? '[file]' : '[redacted]';
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function redact(array $data): array
    {
        $out = $data;
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (!isset($out[$field]) || !is_string($out[$field]) || $out[$field] === '') {
                continue;
            }
            $out[$field] = self::mask($out[$field]);
        }
        return $out;
    }

    private static function mask(string $value): string
    {
        $len = strlen($value);
        if ($len <= 4) {
            return '****';
        }
        return str_repeat('*', $len - 4) . substr($value, -4);
    }
}
