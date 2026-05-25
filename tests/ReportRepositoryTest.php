<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReportRepositoryTest extends TestCase
{
    public function testValidateDateRange(): void
    {
        $this->assertTrue(ReportRepository::validateDateRange('2025-01-01', '2025-12-31'));
        $this->assertFalse(ReportRepository::validateDateRange('2025-12-31', '2025-01-01'));
        $this->assertFalse(ReportRepository::validateDateRange('invalid', '2025-01-01'));
    }

    public function testNormalizeRangeFallsBackOnInvalid(): void
    {
        $r = ReportRepository::normalizeRange('bad', '2025-01-01');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $r['from']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $r['to']);
    }
}
