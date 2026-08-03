<?php

declare(strict_types=1);

final class LeadPickupOptions
{
    /** @return list<string> */
    public static function values(): array
    {
        return [
            'Aeroporto MIA',
            'Aeroporto MCO',
            'Entrega no hotel',
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /** @return list<array{value:string,label:string}> */
    public static function choices(): array
    {
        return [
            ['value' => 'Aeroporto MIA', 'label' => Lang::get('landing.pickup_mia')],
            ['value' => 'Aeroporto MCO', 'label' => Lang::get('landing.pickup_mco')],
            ['value' => 'Entrega no hotel', 'label' => Lang::get('landing.pickup_hotel')],
        ];
    }
}
