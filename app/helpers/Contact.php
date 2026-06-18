<?php

declare(strict_types=1);

final class Contact
{
    public static function whatsapp(): string
    {
        return trim((string) ($_ENV['CONTACT_WHATSAPP'] ?? '5511999999999'));
    }

    public static function phoneDisplay(): string
    {
        return trim((string) ($_ENV['CONTACT_PHONE'] ?? '(11) 4002-8822'));
    }

    public static function phoneTel(): string
    {
        $raw = preg_replace('/\D/', '', self::phoneDisplay()) ?: '551140028822';
        return str_starts_with($raw, '55') ? '+' . $raw : '+55' . $raw;
    }

    public static function email(): string
    {
        return trim((string) ($_ENV['CONTACT_EMAIL'] ?? 'reservas@titaniumrental.com.br'));
    }

    public static function whatsappUrl(string $message = ''): string
    {
        $msg = $message !== '' ? $message : 'Olá, gostaria de alugar um carro (Titanium).';
        return 'https://wa.me/' . preg_replace('/\D/', '', self::whatsapp()) . '?text=' . rawurlencode($msg);
    }
}
