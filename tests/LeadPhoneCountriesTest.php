<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LeadPhoneCountriesTest extends TestCase
{
    public function testComposeBrazil(): void
    {
        $this->assertSame('5511999887766', LeadPhoneCountries::compose('BR', '(11) 99988-7766'));
        $this->assertSame('5511999887766', LeadPhoneCountries::compose('BR', '011999887766'));
    }

    public function testComposeUs(): void
    {
        $this->assertSame('13055551234', LeadPhoneCountries::compose('US', '305-555-1234'));
    }

    public function testDoesNotDoubleDial(): void
    {
        $this->assertSame('5511999887766', LeadPhoneCountries::compose('BR', '5511999887766'));
    }

    public function testRejectsInvalid(): void
    {
        $this->assertNull(LeadPhoneCountries::compose('ZZ', '119999'));
        $this->assertNull(LeadPhoneCountries::compose('BR', '12'));
        $this->assertNull(LeadPhoneCountries::compose('BR', ''));
    }

    public function testIsValid(): void
    {
        $this->assertTrue(LeadPhoneCountries::isValid('BR'));
        $this->assertFalse(LeadPhoneCountries::isValid('XX'));
    }
}
