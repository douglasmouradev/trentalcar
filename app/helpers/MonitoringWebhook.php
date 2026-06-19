<?php

declare(strict_types=1);

/** Notifica webhook externo (Slack, Discord, etc.) em erros críticos. */
final class MonitoringWebhook
{
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
            'context' => $context,
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
}
