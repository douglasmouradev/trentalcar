<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testOwnerAccessesAnyReservation(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'owner'];
        self::assertTrue(AccessControl::canAccessReservation(['operator_id' => 99]));
    }

    public function testOperatorAccessesOwnReservationOnly(): void
    {
        $_SESSION['user'] = ['id' => 2, 'role' => 'operator'];
        self::assertTrue(AccessControl::canAccessReservation(['operator_id' => 2]));
        self::assertFalse(AccessControl::canAccessReservation(['operator_id' => 3]));
    }

    public function testOwnerAccessesAnyCustomer(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'owner'];
        self::assertTrue(AccessControl::canAccessCustomer(['created_by' => 5]));
    }

    public function testOperatorAccessesOwnCustomerOnly(): void
    {
        $_SESSION['user'] = ['id' => 2, 'role' => 'operator'];
        self::assertTrue(AccessControl::canAccessCustomer(['created_by' => 2]));
        self::assertFalse(AccessControl::canAccessCustomer(['created_by' => 1]));
    }

    public function testInvalidReservationIdReturnsFalse(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'owner'];
        self::assertFalse(AccessControl::canAccessReservationId(0));
    }
}
