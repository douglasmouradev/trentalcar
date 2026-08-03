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
