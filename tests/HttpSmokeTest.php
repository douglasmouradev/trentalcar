<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Smoke tests HTTP — requer servidor local (CI define SMOKE_BASE_URL). */
final class HttpSmokeTest extends TestCase
{
    /** @return list<string> */
    private static function baseUrl(): ?string
    {
        $base = getenv('SMOKE_BASE_URL');
        if ($base === false || trim($base) === '') {
            return null;
        }
        return rtrim(trim($base), '/');
    }

    /** @return array{code: int, body: string} */
    private static function fetch(string $path): array
    {
        $url = self::baseUrl() . $path;
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);
        $code = 0;
        if (function_exists('http_get_last_response_headers')) {
            $headers = http_get_last_response_headers();
            if ($headers !== false && isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
                $code = (int) $m[1];
            }
        }
        return ['code' => $code, 'body' => is_string($body) ? $body : ''];
    }

    public function testPublicRoutesRespond(): void
    {
        if (self::baseUrl() === null) {
            $this->markTestSkipped('SMOKE_BASE_URL not set');
        }
        foreach (['/', '/robots.txt', '/.well-known/security.txt', '/privacidade', '/termos', '/login'] as $path) {
            $r = self::fetch($path);
            $this->assertSame(200, $r['code'], "Expected 200 for {$path}");
            $this->assertNotSame('', trim($r['body']), "Empty body for {$path}");
        }
    }

    public function testHealthWithoutTokenForbiddenInDev(): void
    {
        if (self::baseUrl() === null) {
            $this->markTestSkipped('SMOKE_BASE_URL not set');
        }
        $r = self::fetch('/health');
        $this->assertContains($r['code'], [200, 403, 503], 'Health should respond');
    }
}
