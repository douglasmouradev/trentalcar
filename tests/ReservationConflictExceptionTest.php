<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReservationConflictExceptionTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $e = new ReservationConflictException();
        self::assertInstanceOf(RuntimeException::class, $e);
    }
}
