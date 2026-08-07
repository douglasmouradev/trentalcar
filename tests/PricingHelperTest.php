<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PricingHelperTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/PricingHelper.php';
    }

    public function testWeeklyPromoSevenDays(): void
    {
        $r = PricingHelper::applyWeeklyPromo(7, 100.0, 'economy', 0.0);
        self::assertSame(7, $r['total_days']);
        self::assertSame(5, $r['billable_days']);
        self::assertSame(200.0, $r['discount_applied']);
    }

    public function testNoPromoShortRental(): void
    {
        $r = PricingHelper::applyWeeklyPromo(3, 100.0, 'economy', 10.0);
        self::assertSame(3, $r['billable_days']);
        self::assertSame(10.0, $r['discount_applied']);
    }

    public function testRentalDaysUses24HourPeriods(): void
    {
        self::assertSame(1, PricingHelper::rentalDays('2026-08-07', '09:00', '2026-08-07', '18:00'));
        self::assertSame(1, PricingHelper::rentalDays('2026-08-07', '09:00:00', '2026-08-08', '09:00:00'));
        self::assertSame(2, PricingHelper::rentalDays('2026-08-07', '09:00', '2026-08-08', '09:01'));
        self::assertSame(1, PricingHelper::rentalDaysFromDates('2026-08-07', '2026-08-08'));
        self::assertSame(3, PricingHelper::rentalDaysFromDates('2026-08-07', '2026-08-10'));
    }
}
