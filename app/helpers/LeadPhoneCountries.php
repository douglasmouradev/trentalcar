<?php

declare(strict_types=1);

final class LeadPhoneCountries
{
    /** @return list<array{iso:string,dial:string,label_key:string}> */
    public static function all(): array
    {
        return [
            ['iso' => 'BR', 'dial' => '55', 'label_key' => 'landing.country_br'],
            ['iso' => 'US', 'dial' => '1', 'label_key' => 'landing.country_us'],
            ['iso' => 'PT', 'dial' => '351', 'label_key' => 'landing.country_pt'],
            ['iso' => 'AR', 'dial' => '54', 'label_key' => 'landing.country_ar'],
            ['iso' => 'CO', 'dial' => '57', 'label_key' => 'landing.country_co'],
            ['iso' => 'MX', 'dial' => '52', 'label_key' => 'landing.country_mx'],
            ['iso' => 'CL', 'dial' => '56', 'label_key' => 'landing.country_cl'],
            ['iso' => 'ES', 'dial' => '34', 'label_key' => 'landing.country_es'],
            ['iso' => 'IT', 'dial' => '39', 'label_key' => 'landing.country_it'],
            ['iso' => 'DE', 'dial' => '49', 'label_key' => 'landing.country_de'],
            ['iso' => 'FR', 'dial' => '33', 'label_key' => 'landing.country_fr'],
            ['iso' => 'GB', 'dial' => '44', 'label_key' => 'landing.country_gb'],
            ['iso' => 'CA', 'dial' => '1', 'label_key' => 'landing.country_ca'],
            ['iso' => 'UY', 'dial' => '598', 'label_key' => 'landing.country_uy'],
            ['iso' => 'PY', 'dial' => '595', 'label_key' => 'landing.country_py'],
        ];
    }

    public static function isValid(string $iso): bool
    {
        foreach (self::all() as $c) {
            if ($c['iso'] === $iso) {
                return true;
            }
        }
        return false;
    }

    public static function dialFor(string $iso): ?string
    {
        foreach (self::all() as $c) {
            if ($c['iso'] === $iso) {
                return $c['dial'];
            }
        }
        return null;
    }

    public static function defaultIso(): string
    {
        $lang = Lang::locale();
        return str_starts_with($lang, 'pt') ? 'BR' : 'US';
    }

    /**
     * Monta número internacional (só dígitos, com DDI) a partir do país e do nacional.
     * Aceita nacional com ou sem zeros à esquerda / máscara.
     */
    public static function compose(string $iso, string $national): ?string
    {
        $dial = self::dialFor($iso);
        if ($dial === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $national) ?? '';
        if ($digits === '') {
            return null;
        }
        // Se o usuário colou o número já com DDI, evita duplicar.
        if (str_starts_with($digits, $dial) && strlen($digits) > strlen($dial) + 6) {
            $full = $digits;
        } else {
            $digits = ltrim($digits, '0');
            if ($digits === '') {
                return null;
            }
            $full = $dial . $digits;
        }
        if (strlen($full) < 10 || strlen($full) > 15) {
            return null;
        }
        return $full;
    }

    /** @return list<array{iso:string,dial:string,label:string}> */
    public static function choices(): array
    {
        $out = [];
        foreach (self::all() as $c) {
            $out[] = [
                'iso' => $c['iso'],
                'dial' => $c['dial'],
                'label' => Lang::get($c['label_key']) . ' (+' . $c['dial'] . ')',
            ];
        }
        return $out;
    }
}
