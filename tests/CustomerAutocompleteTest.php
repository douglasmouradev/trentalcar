<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CustomerAutocompleteTest extends TestCase
{
    public function testMaskDocumentHidesMiddleDigits(): void
    {
        $ref = new ReflectionClass(Customer::class);
        $method = $ref->getMethod('maskDocument');
        $method->setAccessible(true);

        self::assertSame('*******8901', $method->invoke(null, '12345678901'));
        self::assertSame('****', $method->invoke(null, '123'));
    }
}
