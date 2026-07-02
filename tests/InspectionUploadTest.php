<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InspectionUploadTest extends TestCase
{
    public function testAbsolutePathRejectsForeignReservationPrefix(): void
    {
        $this->assertNull(InspectionUpload::absolutePath(5, 'r99-pickup-abc.jpg'));
    }

    public function testDeleteStoredIgnoresEmpty(): void
    {
        InspectionUpload::deleteStored(null);
        InspectionUpload::deleteStored('');
        $this->assertTrue(true);
    }

    public function testUrlContainsReservationId(): void
    {
        $_ENV['APP_URL'] = 'http://localhost';
        $url = InspectionUpload::url(12, 'r12-pickup-deadbeef.jpg');
        $this->assertStringContainsString('/reservations/12/inspection-photo', $url);
    }
}
