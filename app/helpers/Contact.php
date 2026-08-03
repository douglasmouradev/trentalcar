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
        return trim((string) ($_ENV['CONTACT_PHONE'] ?? '(11) 3000-1000'));
    }

    public static function phoneTel(): string
    {
        $raw = preg_replace('/\D/', '', self::phoneDisplay()) ?: '551130001000';
        return str_starts_with($raw, '55') ? '+' . $raw : '+55' . $raw;
    }

    public static function email(): string
    {
        return trim((string) ($_ENV['CONTACT_EMAIL'] ?? 'contato@titaniumrentalcar.com'));
    }

    public static function legalName(): string
    {
        $name = trim((string) ($_ENV['BUSINESS_LEGAL_NAME'] ?? ''));
        if ($name !== '') {
            return $name;
        }
        return trim((string) ($_ENV['PRIVACY_CONTROLLER_NAME'] ?? 'Titanium Rental Car Ltda'));
    }

    public static function ein(): string
    {
        foreach (['BUSINESS_EIN', 'PRIVACY_CONTROLLER_EIN'] as $key) {
            $value = trim((string) ($_ENV[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '61-2244130';
    }

    public static function address(): string
    {
        $addr = trim((string) ($_ENV['CONTACT_ADDRESS'] ?? ''));
        if ($addr !== '') {
            return $addr;
        }
        return trim((string) ($_ENV['PRIVACY_ADDRESS'] ?? '400 W Church St — Downtown, Orlando — FL'));
    }

    public static function businessHours(): string
    {
        return trim((string) ($_ENV['BUSINESS_HOURS'] ?? 'Domingo a domingo, 24 horas'));
    }

    public static function minDailyRate(): string
    {
        return trim((string) ($_ENV['BUSINESS_MIN_RATE'] ?? '$99.90'));
    }

    public static function responseTime(): string
    {
        return trim((string) ($_ENV['BUSINESS_RESPONSE_TIME'] ?? '2 horas úteis'));
    }

    public static function whatsappUrl(string $message = ''): string
    {
        $msg = $message !== '' ? $message : Lang::get('contact.whatsapp_default');
        return 'https://wa.me/' . preg_replace('/\D/', '', self::whatsapp()) . '?text=' . rawurlencode($msg);
    }

    /** Linha única para rodapé / aviso legal. */
    public static function footerLegalLine(): string
    {
        return implode(' · ', [self::legalName(), self::address()]);
    }
}
