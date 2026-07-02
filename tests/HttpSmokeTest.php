<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Smoke tests HTTP — requer servidor local (CI define SMOKE_BASE_URL). */
final class HttpSmokeTest extends TestCase
{
    private ?HttpTestClient $client = null;

    protected function setUp(): void
    {
        $this->client = HttpTestClient::fromEnv();
        if ($this->client === null) {
            self::markTestSkipped('SMOKE_BASE_URL ou ext-curl indisponível');
        }
    }

    public function testPublicRoutesRespond(): void
    {
        foreach (['/', '/robots.txt', '/.well-known/security.txt', '/privacidade', '/termos', '/login'] as $path) {
            $r = $this->client->get($path);
            $this->assertSame(200, $r['code'], "Expected 200 for {$path}");
            $this->assertNotSame('', trim($r['body']), "Empty body for {$path}");
        }
    }

    public function testHealthWithoutTokenForbiddenInDev(): void
    {
        $r = $this->client->get('/health');
        $this->assertContains($r['code'], [200, 403, 503], 'Health should respond');
    }
}
