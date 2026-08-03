<?php

declare(strict_types=1);

final class ExchangeRate
{
    private const API_URL = 'https://economia.awesomeapi.com.br/json/last/USD-BRL';
    private const CACHE_TTL = 86400;
    private const DEFAULT_FALLBACK = 5.50;

    private static ?float $memo = null;

    public static function cachePath(): string
    {
        return BASE_PATH . '/storage/cache/usd_brl.json';
    }

    public static function fallbackRate(): float
    {
        $raw = trim((string) ($_ENV['USD_BRL_FALLBACK'] ?? ''));
        if ($raw !== '' && is_numeric($raw)) {
            $v = (float) $raw;
            if ($v > 0) {
                return $v;
            }
        }
        return self::DEFAULT_FALLBACK;
    }

    /** USD → BRL (quantos reais por 1 dólar). */
    public static function rate(): float
    {
        if (self::$memo !== null) {
            return self::$memo;
        }
        $cached = self::readCache();
        if ($cached !== null && !self::isExpired($cached)) {
            self::$memo = (float) $cached['rate'];
            return self::$memo;
        }
        if (self::refresh(false)) {
            $cached = self::readCache();
            if ($cached !== null) {
                self::$memo = (float) $cached['rate'];
                return self::$memo;
            }
        }
        if ($cached !== null) {
            self::$memo = (float) $cached['rate'];
            return self::$memo;
        }
        self::$memo = self::fallbackRate();
        return self::$memo;
    }

    public static function toBrl(float $usd): float
    {
        return round($usd * self::rate(), 2);
    }

    /** @return array{rate:float,fetched_at:int,source:string}|null */
    public static function meta(): ?array
    {
        $cached = self::readCache();
        if ($cached === null) {
            return null;
        }
        return $cached;
    }

    public static function refresh(bool $force = false): bool
    {
        if (!$force) {
            $cached = self::readCache();
            if ($cached !== null && !self::isExpired($cached)) {
                return true;
            }
        }

        $rate = self::fetchFromApi();
        if ($rate === null || $rate <= 0) {
            return false;
        }

        $dir = dirname(self::cachePath());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $payload = [
            'rate' => round($rate, 6),
            'fetched_at' => time(),
            'source' => 'awesomeapi',
        ];
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $ok = @file_put_contents(self::cachePath(), $json, LOCK_EX) !== false;
        if ($ok) {
            self::$memo = (float) $payload['rate'];
        }
        return $ok;
    }

    /** @internal tests */
    public static function resetMemo(): void
    {
        self::$memo = null;
    }

    /** @param array{rate:float,fetched_at:int,source:string} $cached */
    private static function isExpired(array $cached): bool
    {
        return (time() - (int) $cached['fetched_at']) >= self::CACHE_TTL;
    }

    /** @return array{rate:float,fetched_at:int,source:string}|null */
    private static function readCache(): ?array
    {
        $path = self::cachePath();
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        try {
            /** @var array<string,mixed> $data */
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
        $rate = (float) ($data['rate'] ?? 0);
        $fetched = (int) ($data['fetched_at'] ?? 0);
        if ($rate <= 0 || $fetched <= 0) {
            return null;
        }
        return [
            'rate' => $rate,
            'fetched_at' => $fetched,
            'source' => (string) ($data['source'] ?? 'cache'),
        ];
    }

    private static function fetchFromApi(): ?float
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: TitaniumRentalCar/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $raw = @file_get_contents(self::API_URL, false, $ctx);
        if ($raw === false || $raw === '') {
            return null;
        }
        try {
            /** @var array<string,mixed> $data */
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
        $row = $data['USDBRL'] ?? null;
        if (!is_array($row)) {
            return null;
        }
        $bid = $row['bid'] ?? null;
        if (!is_numeric($bid)) {
            return null;
        }
        $rate = (float) $bid;
        return $rate > 0 ? $rate : null;
    }
}
