<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/Formatter.php';

final class FormatterTest extends TestCase
{
    public function testMoneyFormat(): void
    {
        $this->assertSame('R$ 1.234,56', Formatter::money(1234.56));
    }

    public function testCpfMask(): void
    {
        $this->assertSame('123.456.789-01', Formatter::document('12345678901'));
    }

    public function testPhoneMask(): void
    {
        $this->assertSame('(11) 98765-4321', Formatter::phone('11987654321'));
    }

    public function testPercentDelta(): void
    {
        $this->assertSame('+50%', Formatter::percentDelta(150, 100));
        $this->assertSame('-25%', Formatter::percentDelta(75, 100));
    }
}
