<?php

declare(strict_types=1);

final class PricingHelper
{
    /** Categorias elegíveis à promo "7 dias pelo preço de 5" */
    private const PROMO_CATEGORIES = ['economy', 'compact', 'standard'];

    /**
     * Conta diárias por períodos de 24h (mínimo 1).
     * Ex.: 07/08 09:00 → 08/08 09:00 = 1 diária; 07/08 09:00 → 08/08 09:01 = 2.
     */
    public static function rentalDays(
        string $pickupDate,
        string $pickupTime,
        string $returnDate,
        string $returnTime
    ): int {
        $pickupTime = self::normalizeTime($pickupTime);
        $returnTime = self::normalizeTime($returnTime);
        try {
            $start = new DateTimeImmutable(trim($pickupDate) . ' ' . $pickupTime);
            $end = new DateTimeImmutable(trim($returnDate) . ' ' . $returnTime);
        } catch (Exception) {
            return 1;
        }
        if ($end <= $start) {
            return 1;
        }
        $seconds = $end->getTimestamp() - $start->getTimestamp();

        return max(1, (int) ceil($seconds / 86400));
    }

    /**
     * Estimativa só com datas (site): assume mesma hora nos dois dias → noites/24h.
     * 07/08→07/08 = 1; 07/08→08/08 = 1; 07/08→10/08 = 3.
     */
    public static function rentalDaysFromDates(string $pickupDate, string $returnDate): int
    {
        return self::rentalDays($pickupDate, '12:00:00', $returnDate, '12:00:00');
    }

    private static function normalizeTime(string $time): string
    {
        $time = trim($time);
        if ($time === '') {
            return '12:00:00';
        }
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) === 1) {
            return $time;
        }

        return '12:00:00';
    }

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
