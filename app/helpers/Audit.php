<?php

declare(strict_types=1);

final class Audit
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_hash',
        'document',
        'cpf',
        'cnpj',
        'phone',
        'email',
        'contact_email',
        'contact_phone',
        'contact_name',
        'full_name',
        'address',
        'notes',
    ];

    public static function log(
        ?int $userId,
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $ipRaw = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ip = $ipRaw !== '' ? hash('sha256', $ipRaw) : null;
        $stmt = Database::pdo()->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, old_data, new_data, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $entity,
            $entityId,
            $oldData === null ? null : json_encode(self::sanitize($oldData), JSON_THROW_ON_ERROR),
            $newData === null ? null : json_encode(self::sanitize($newData), JSON_THROW_ON_ERROR),
            $ip,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function sanitize(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (in_array($key, self::SENSITIVE_KEYS, true)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if ($key === 'attachment_path' || $key === 'image_path') {
                $out[$key] = $value ? '[file]' : null;
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }
}
