<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CustomerServiceTest extends TestCase
{
    public function testExportPayloadNullWhenMissing(): void
    {
        $this->assertNull(CustomerService::exportPayload(999999999));
    }
}
