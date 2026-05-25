<?php

declare(strict_types=1);

final class RequestId
{
    private static ?string $id = null;

    public static function get(): string
    {
        if (self::$id === null) {
            self::$id = bin2hex(random_bytes(8));
        }
        return self::$id;
    }
}
