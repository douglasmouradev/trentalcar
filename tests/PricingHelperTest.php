<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PricingHelperTest extends TestCase
{
    public function testWeeklyPromoSevenDays(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/PricingHelper.php';
        $r = PricingHelper::applyWeeklyPromo(7, 100.0, 'economy', 0.0);
        self::assertSame(7, $r['total_days']);
        self::assertSame(5, $r['billable_days']);
        self::assertSame(200.0, $r['discount_applied']);
    }

    public function testNoPromoShortRental(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/PricingHelper.php';
        $r = PricingHelper::applyWeeklyPromo(3, 100.0, 'economy', 10.0);
        self::assertSame(3, $r['billable_days']);
        self::assertSame(10.0, $r['discount_applied']);
    }
}
