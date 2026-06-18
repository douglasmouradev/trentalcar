<?php

declare(strict_types=1);

final class PricingHelper
{
    /** Categorias elegíveis à promo "7 dias pelo preço de 5" */
    private const PROMO_CATEGORIES = ['economy', 'compact', 'standard'];

    /**
     * Aplica promo semanal quando aplicável.
     *
     * @return array{total_days: int, billable_days: int, discount_applied: float}
     */
    public static function applyWeeklyPromo(int $totalDays, float $dailyRate, string $category, float $existingDiscount = 0.0): array
    {
        $billable = $totalDays;
        $promoDiscount = 0.0;
        if ($totalDays >= 7 && in_array(strtolower($category), self::PROMO_CATEGORIES, true)) {
            $billable = 5 + max(0, $totalDays - 7);
            $promoDiscount = max(0.0, round(($totalDays - $billable) * $dailyRate, 2));
        }
        return [
            'total_days' => $totalDays,
            'billable_days' => $billable,
            'discount_applied' => round($existingDiscount + $promoDiscount, 2),
        ];
    }
}
