<?php

declare(strict_types=1);

/**
 * Fallback temporário quando o INSERT do lead falha — PII cifrada ou apenas hashes (LGPD).
 */
final class LeadJsonlFallback
{
    private const FILE = 'leads.jsonl';
    private const TTL_DAYS = 7;

    /**
     * @param array<string, mixed> $payload
     */
    public static function append(array $payload): void
    {
        $dir = BASE_PATH . '/storage/leads';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $record = [
            'at' => gmdate('c'),
            'ip_hash' => hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '')),
        ];

        if (SecretCipher::encryptionAvailable()) {
            $record['enc'] = SecretCipher::encrypt(json_encode($payload, JSON_THROW_ON_ERROR));
        } else {
            $record['email_hash'] = isset($payload['email']) && is_string($payload['email'])
                ? hash('sha256', strtolower(trim($payload['email'])))
                : null;
            $record['phone_hash'] = isset($payload['phone']) && is_string($payload['phone'])
                ? hash('sha256', preg_replace('/\D/', '', $payload['phone']) ?? '')
                : null;
        }

        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
        file_put_contents($dir . '/' . self::FILE, $line, FILE_APPEND | LOCK_EX);

        AppLog::error('lead.jsonl_fallback', ['encrypted' => isset($record['enc'])]);
        MonitoringWebhook::notify('lead.jsonl_fallback', [
            'encrypted' => isset($record['enc']),
            'at' => $record['at'],
        ]);
    }

    public static function purgeExpired(): int
    {
        $path = BASE_PATH . '/storage/leads/' . self::FILE;
        if (!is_file($path)) {
            return 0;
        }
        $cutoff = time() - (self::TTL_DAYS * 86400);
        $kept = [];
        $removed = 0;
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return 0;
        }
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            try {
                /** @var array<string, mixed> $row */
                $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                $at = isset($row['at']) ? strtotime((string) $row['at']) : false;
                if ($at !== false && $at >= $cutoff) {
                    $kept[] = $line;
                } else {
                    $removed++;
                }
            } catch (Throwable) {
                $removed++;
            }
        }
        if ($removed > 0) {
            file_put_contents($path, $kept === [] ? '' : implode("\n", $kept) . "\n", LOCK_EX);
        }
        return $removed;
    }
}
