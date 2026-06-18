<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecretCipherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['APP_KEY'] = 'test-key-for-unit-tests-only';
        require_once BASE_PATH . '/app/helpers/SecretCipher.php';
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('sodium extension not available');
        }
        $plain = 'JBSWY3DPEHPK3PXP';
        $enc = SecretCipher::encrypt($plain);
        self::assertTrue(SecretCipher::isEncrypted($enc));
        self::assertSame($plain, SecretCipher::decrypt($enc));
    }

    public function testLegacyPlaintextPassthrough(): void
    {
        $plain = 'JBSWY3DPEHPK3PXP';
        self::assertSame($plain, SecretCipher::decrypt($plain));
    }
}
