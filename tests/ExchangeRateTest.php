<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/ExchangeRate.php';

final class ExchangeRateTest extends TestCase
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
    }

    public function testRateUsesFreshCacheWithoutNetwork(): void
    {
        file_put_contents(
            ExchangeRate::cachePath(),
            json_encode([
                'rate' => 4.25,
                'fetched_at' => time(),
                'source' => 'test',
            ], JSON_THROW_ON_ERROR)
        );
        ExchangeRate::resetMemo();
        $this->assertSame(4.25, ExchangeRate::rate());
        $this->assertSame(42.5, ExchangeRate::toBrl(10.0));
    }

    public function testFallbackWhenCacheMissing(): void
    {
        $path = ExchangeRate::cachePath();
        if (is_file($path)) {
            @unlink($path);
        }
        $_ENV['USD_BRL_FALLBACK'] = '6.10';
        ExchangeRate::resetMemo();
        // Sem cache e sem rede confiável no CI: rate() tenta API e cai no fallback.
        $rate = ExchangeRate::rate();
        $this->assertGreaterThan(0, $rate);
    }
}
