<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReservationCarSyncTest extends TestCase
{
    public function testReconcileCarStatusMethodExists(): void
    {
        $this->assertTrue(method_exists(Reservation::class, 'reconcileCarStatus'));
    }

    public function testLeadStatusesAreDefined(): void
    {
        $this->assertContains('new', Lead::STATUSES);
        $this->assertContains('converted', Lead::STATUSES);
    }
}
