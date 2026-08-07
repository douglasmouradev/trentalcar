<?php

declare(strict_types=1);

final class ExchangeRate
{
    /** Atualiza no máximo a cada hora (cron a cada 5 min + pedidos HTTP). */
    private const CACHE_TTL = 3600;

    /** Fallback alinhado à faixa recente USD/BRL (~Google Finance). */
    private const DEFAULT_FALLBACK = 5.10;

    private const SOURCES = [
        [
            'name' => 'awesomeapi',
            'url' => 'https://economia.awesomeapi.com.br/json/last/USD-BRL',
            'parse' => 'parseAwesomeApi',
        ],
        [
            'name' => 'exchangerate-api',
            'url' => 'https://open.er-api.com/v6/latest/USD',
            'parse' => 'parseOpenErApi',
        ],
        [
            'name' => 'frankfurter',
            'url' => 'https://api.frankfurter.app/latest?from=USD&to=BRL',
            'parse' => 'parseFrankfurter',
        ],
    ];

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

    /** Texto curto para UI: "USD 1 = R$ 5,10". */
    public static function label(): string
    {
        $rate = self::rate();
        $brl = number_format($rate, 2, ',', '.');
        return 'USD 1 = R$ ' . $brl;
    }

    /** @return array{rate:float,fetched_at:int,source:string}|null */
    public static function meta(): ?array
    {
        return self::readCache();
    }

    public static function refresh(bool $force = false): bool
    {
        if (!$force) {
            $cached = self::readCache();
            if ($cached !== null && !self::isExpired($cached)) {
                return true;
            }
        }

        $fetched = self::fetchFromApis();
        if ($fetched === null) {
            if (class_exists('AppLog')) {
                AppLog::error('exchange.rate_fetch_failed', ['fallback' => self::fallbackRate()]);
            }
            return false;
        }

        $dir = dirname(self::cachePath());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $payload = [
            'rate' => round($fetched['rate'], 6),
            'fetched_at' => time(),
            'source' => $fetched['source'],
        ];
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return false;
        }
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

    /** @return array{rate:float,source:string}|null */
    private static function fetchFromApis(): ?array
    {
        foreach (self::SOURCES as $source) {
            $raw = self::httpGet($source['url']);
            if ($raw === null || $raw === '') {
                continue;
            }
            try {
                /** @var array<string,mixed> $data */
                $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                continue;
            }
            $rate = match ($source['parse']) {
                'parseAwesomeApi' => self::parseAwesomeApi($data),
                'parseOpenErApi' => self::parseOpenErApi($data),
                'parseFrankfurter' => self::parseFrankfurter($data),
                default => null,
            };
            if ($rate !== null && $rate > 0) {
                return ['rate' => $rate, 'source' => $source['name']];
            }
        }
        return null;
    }

    /** @param array<string,mixed> $data */
    private static function parseAwesomeApi(array $data): ?float
    {
        $row = $data['USDBRL'] ?? null;
        if (!is_array($row)) {
            return null;
        }
        $bid = $row['bid'] ?? null;
        return is_numeric($bid) ? (float) $bid : null;
    }

    /** @param array<string,mixed> $data */
    private static function parseOpenErApi(array $data): ?float
    {
        if (($data['result'] ?? '') !== 'success') {
            return null;
        }
        $rates = $data['rates'] ?? null;
        if (!is_array($rates)) {
            return null;
        }
        $brl = $rates['BRL'] ?? null;
        return is_numeric($brl) ? (float) $brl : null;
    }

    /** @param array<string,mixed> $data */
    private static function parseFrankfurter(array $data): ?float
    {
        $rates = $data['rates'] ?? null;
        if (!is_array($rates)) {
            return null;
        }
        $brl = $rates['BRL'] ?? null;
        return is_numeric($brl) ? (float) $brl : null;
    }

    private static function httpGet(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_TIMEOUT => 8,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'User-Agent: TitaniumRentalCar/1.0',
                    ],
                ]);
                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (is_string($body) && $body !== '' && $code >= 200 && $code < 300) {
                    return $body;
                }
            }
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: TitaniumRentalCar/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || $raw === '') {
            return null;
        }
        return $raw;
    }
}
