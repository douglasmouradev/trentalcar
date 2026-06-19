<?php

declare(strict_types=1);

final class ApiJson
{
    /** @param array<string, mixed> $payload */
    public static function send(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_THROW_ON_ERROR);
        exit;
    }

    /** @param array<string, mixed> $data */
    public static function ok(array $data = [], int $status = 200): never
    {
        self::send($status, ['ok' => true] + $data);
    }

    /** @param array<string, mixed> $extra */
    public static function fail(string $error, int $status = 400, array $extra = []): never
    {
        self::send($status, ['ok' => false, 'error' => $error] + $extra);
    }
}
