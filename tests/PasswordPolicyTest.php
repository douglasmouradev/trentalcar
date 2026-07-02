<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PasswordPolicyTest extends TestCase
{
    public function testAcceptsStrongPassword(): void
    {
        $this->assertNull(PasswordPolicy::validate('Segura2025!'));
    }

    public function testRejectsShortPassword(): void
    {
        $this->assertSame(Lang::get('user.password_short'), PasswordPolicy::validate('Ab1'));
    }

    public function testRejectsWithoutDigit(): void
    {
        $this->assertSame(Lang::get('auth.password_complexity'), PasswordPolicy::validate('seguraforte'));
    }
}
