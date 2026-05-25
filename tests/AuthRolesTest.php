<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuthRolesTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testOwnerRole(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'owner', 'name' => 'O', 'email' => 'o@test.com'];
        $this->assertTrue(Auth::isOwner());
        $this->assertFalse(Auth::isPartner());
    }

    public function testPartnerRole(): void
    {
        $_SESSION['user'] = ['id' => 2, 'role' => 'partner', 'name' => 'P', 'email' => 'p@test.com', 'car_ids' => [1, 2]];
        $this->assertFalse(Auth::isOwner());
        $this->assertTrue(Auth::isPartner());
        $this->assertSame([1, 2], Auth::partnerCarIds());
    }
}
