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
        $this->assertSame('short', PasswordPolicy::validate('Ab1'));
    }

    public function testRejectsWithoutUppercase(): void
    {
        $this->assertSame('upper', PasswordPolicy::validate('segura2025'));
    }
}
