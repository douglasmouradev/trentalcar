<?php

declare(strict_types=1);

/** Notifica webhook externo (Slack, Discord, etc.) em erros críticos. */
final class MonitoringWebhook
{
    /** @var list<string> */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'password',
        'secret',
        'token',
        'email',
        'phone',
        'document',
        'authorization',
        'cookie',
        'csrf',
        'api_key',
        'attachment',
    ];

    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'to',
        'subject',
        'body',
        'full_name',
        'address',
        'notes',
    ];

    /** @param array<string, mixed> $context */
    public static function notify(string $event, array $context = []): void
    {
        $url = trim((string) ($_ENV['MONITORING_WEBHOOK_URL'] ?? ''));
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }

        $payload = json_encode([
            'text' => '[' . (Config::app()['name'] ?? 'Titanium Rental Car') . "] {$event}",
            'event' => $event,
            'time' => gmdate('c'),
            'request_id' => class_exists('RequestId') ? RequestId::get() : null,
            'context' => self::sanitizeContext($context),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 3,
                'ignore_errors' => true,
            ],
        ]);
        @file_get_contents($url, false, $ctx);
    }

    /**
     * Remove PII e segredos antes de enviar a terceiros.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public static function sanitizeContext(array $context): array
    {
        $out = [];
        foreach ($context as $key => $value) {
            $keyName = strtolower((string) $key);
            if (self::isSensitiveKey($keyName)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if ($keyName === 'error' && is_string($value)) {
                $out[$key] = self::sanitizeErrorMessage($value);
                continue;
            }
            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $out[$key] = self::sanitizeContext($value);
                continue;
            }
            if (is_string($value) && strlen($value) > 500) {
                $out[$key] = substr($value, 0, 500) . '…';
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    private static function isSensitiveKey(string $key): bool
    {
        if (in_array($key, self::SENSITIVE_KEYS, true)) {
            return true;
        }
        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($key, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private static function sanitizeErrorMessage(string $message): string
    {
        $message = preg_replace('/password\s*=\s*\S+/i', 'password=[redacted]', $message) ?? $message;
        $message = preg_replace(
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/',
            '[email]',
            $message
        ) ?? $message;

        return mb_strlen($message) > 300 ? mb_substr($message, 0, 300) . '…' : $message;
    }
}
