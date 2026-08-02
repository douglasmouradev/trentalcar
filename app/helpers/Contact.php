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
        return trim((string) ($_ENV['CONTACT_EMAIL'] ?? 'reservas@titaniumrental.com.br'));
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
        return trim((string) ($_ENV['BUSINESS_EIN'] ?? $_ENV['PRIVACY_CONTROLLER_EIN'] ?? $_ENV['BUSINESS_CNPJ'] ?? $_ENV['PRIVACY_CONTROLLER_CNPJ'] ?? '61-2244130'));
    }

    public static function address(): string
    {
        $addr = trim((string) ($_ENV['CONTACT_ADDRESS'] ?? ''));
        if ($addr !== '') {
            return $addr;
        }
        return trim((string) ($_ENV['PRIVACY_ADDRESS'] ?? 'Av. Paulista, 1000 — Bela Vista, São Paulo — SP'));
    }

    public static function businessHours(): string
    {
        return trim((string) ($_ENV['BUSINESS_HOURS'] ?? 'Segunda a sábado, 8h às 18h'));
    }

    public static function minDailyRate(): string
    {
        return trim((string) ($_ENV['BUSINESS_MIN_RATE'] ?? 'R$ 99,90'));
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
        $parts = [self::legalName()];
        $ein = self::ein();
        if ($ein !== '') {
            $parts[] = 'EIN ' . $ein;
        }
        $parts[] = self::address();
        return implode(' · ', $parts);
    }
}
