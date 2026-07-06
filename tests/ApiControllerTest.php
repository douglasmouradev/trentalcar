<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testReservationConflictRejectsInvalidDates(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'owner'];
        $_GET = [
            'car_id' => '1',
            'pickup_date' => 'not-a-date',
            'return_date' => '2099-01-10',
        ];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $controller = new ApiController();
        ob_start();
        try {
            $controller->reservationConflict();
        } finally {
            $body = (string) ob_get_clean();
        }

        $json = json_decode($body, true);
        self::assertIsArray($json);
        self::assertFalse($json['ok']);
        self::assertSame('invalid_dates', $json['error']);
    }
}
