<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/Totp.php';

final class TotpTest extends TestCase
{
    public function testGenerateAndVerify(): void
    {
        $secret = Totp::generateSecret();
        $this->assertGreaterThan(16, strlen($secret));
        $ref = new ReflectionClass(Totp::class);
        $method = $ref->getMethod('codeAt');
        $method->setAccessible(true);
        $code = $method->invoke(null, $secret, (int) floor(time() / 30));
        $this->assertTrue(Totp::verify($secret, $code));
        $this->assertFalse(Totp::verify($secret, '000000'));
    }
}
