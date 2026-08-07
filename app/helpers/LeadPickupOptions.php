<?php

declare(strict_types=1);

final class LeadPickupOptions
{
    public const HOTEL = 'Entrega no hotel';

    /** @return list<string> */
    public static function values(): array
    {
        return [
            'Aeroporto MIA',
            'Aeroporto MCO',
            self::HOTEL,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function isHotel(string $value): bool
    {
        return $value === self::HOTEL;
    }

    /** Guarda o nome do hotel junto da opção para o painel / WhatsApp. */
    public static function withHotelName(string $option, string $hotelName): string
    {
        $hotelName = trim($hotelName);
        if (!self::isHotel($option) || $hotelName === '') {
            return $option;
        }

        return $option . ' — ' . $hotelName;
    }

    /** Extrai o nome do hotel de um valor armazenado (ex.: "Entrega no hotel — Hilton"). */
    public static function hotelNameFromStored(string $stored): string
    {
        $stored = trim($stored);
        if ($stored === '' || !str_starts_with($stored, self::HOTEL)) {
            return '';
        }
        if (preg_match('/^' . preg_quote(self::HOTEL, '/') . '\s+[—\-]\s+(.+)$/u', $stored, $m) === 1) {
            return trim($m[1]);
        }

        return '';
    }

    /** @return list<array{value:string,label:string}> */
    public static function choices(): array
    {
        return [
            ['value' => 'Aeroporto MIA', 'label' => Lang::get('landing.pickup_mia')],
            ['value' => 'Aeroporto MCO', 'label' => Lang::get('landing.pickup_mco')],
            ['value' => self::HOTEL, 'label' => Lang::get('landing.pickup_hotel')],
        ];
    }
}
