<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/Formatter.php';
require_once __DIR__ . '/../app/helpers/ExchangeRate.php';

final class FormatterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }
        ExchangeRate::resetMemo();
        $dir = BASE_PATH . '/storage/cache';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            ExchangeRate::cachePath(),
            json_encode([
                'rate' => 5.0,
                'fetched_at' => time(),
                'source' => 'test',
            ], JSON_THROW_ON_ERROR)
        );
        ExchangeRate::resetMemo();
    }

    public function testMoneyAlwaysUsd(): void
    {
        $this->assertSame('$1,234.56', Formatter::money(1234.56, 'pt-BR'));
        $this->assertSame('$1,234.56', Formatter::money(1234.56, 'en-US'));
        $this->assertSame('$1,234.56', Formatter::money(1234.56));
    }

    public function testMoneyWithBrl(): void
    {
        $this->assertSame('$100.00 ≈ R$ 500,00', Formatter::moneyWithBrl(100.0));
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
