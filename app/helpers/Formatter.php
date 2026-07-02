<?php

declare(strict_types=1);

final class Formatter
{
    public static function money(float $value, ?string $locale = null): string
    {
        $locale = $locale ?? (class_exists('Lang', false) ? Lang::locale() : 'pt-BR');
        if ($locale === 'en-US') {
            return '$' . number_format($value, 2, '.', ',');
        }

        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public static function document(string $digits): string
    {
        $d = preg_replace('/\D/', '', $digits) ?? '';
        if (strlen($d) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d) ?? $digits;
        }
        if (strlen($d) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d) ?? $digits;
        }
        return $digits;
    }

    public static function phone(string $value): string
    {
        $d = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($d) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d) ?? $value;
        }
        if (strlen($d) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d) ?? $value;
        }
        return $value;
    }

    public static function percentDelta(float $current, float $previous): ?string
    {
        if ($previous <= 0.0) {
            return $current > 0 ? '+100%' : null;
        }
        $delta = (($current - $previous) / $previous) * 100;
        $sign = $delta >= 0 ? '+' : '';
        return $sign . number_format($delta, 0, ',', '.') . '%';
    }
}
