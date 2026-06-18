<?php

declare(strict_types=1);

/** Cifra simétrica para segredos em repouso (ex.: TOTP). */
final class SecretCipher
{
    private const PREFIX = 'enc1:';

    public static function encrypt(string $plain): string
    {
        $key = self::deriveKey();
        if ($key === null || !function_exists('sodium_crypto_secretbox')) {
            return $plain;
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plain, $nonce, $key);
        return self::PREFIX . base64_encode($nonce . $cipher);
    }

    public static function decrypt(string $stored): string
    {
        if (!str_starts_with($stored, self::PREFIX)) {
            return $stored;
        }
        $key = self::deriveKey();
        if ($key === null || !function_exists('sodium_crypto_secretbox_open')) {
            throw new RuntimeException('APP_KEY required to decrypt secrets');
        }
        $raw = base64_decode(substr($stored, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            throw new RuntimeException('Invalid encrypted secret');
        }
        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        if ($plain === false) {
            throw new RuntimeException('Failed to decrypt secret');
        }
        return $plain;
    }

    public static function isEncrypted(string $stored): bool
    {
        return str_starts_with($stored, self::PREFIX);
    }

    private static function deriveKey(): ?string
    {
        $raw = trim((string) ($_ENV['APP_KEY'] ?? ''));
        if ($raw === '') {
            return null;
        }
        return hash('sha256', $raw, true);
    }
}
