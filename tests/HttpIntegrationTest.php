<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Fluxos HTTP críticos — requer SMOKE_BASE_URL e ext-curl (CI). */
final class HttpIntegrationTest extends TestCase
{
    private ?HttpTestClient $client = null;

    protected function setUp(): void
    {
        $this->client = HttpTestClient::fromEnv();
        if ($this->client === null) {
            self::markTestSkipped('SMOKE_BASE_URL ou ext-curl indisponível');
        }
    }

    public function testLeadSubmitWithoutCsrfRedirectsToError(): void
    {
        $r = $this->client->post('/lead', [
            'nome' => 'Teste HTTP',
            'email' => 'http-lead@example.com',
            'telefone' => '11999990000',
            'local' => 'Centro',
            'inicio' => '2099-10-01',
            'fim' => '2099-10-05',
            'mesmo_local' => '1',
            '_return' => '/',
        ]);
        $this->assertContains($r['code'], [302, 303]);
        $this->assertStringContainsString('lead=erro', (string) $r['location']);
    }

    public function testLeadSubmitValidRedirectsToSuccess(): void
    {
        $home = $this->client->get('/');
        $this->assertSame(200, $home['code']);
        $csrf = $this->client->extractCsrf($home['body']);
        $this->assertNotNull($csrf);

        $r = $this->client->post('/lead', [
            '_csrf' => $csrf,
            'nome' => 'Teste HTTP',
            'email' => 'http-lead-' . bin2hex(random_bytes(3)) . '@example.com',
            'telefone' => '11999990000',
            'local' => 'Centro',
            'inicio' => '2099-11-01',
            'fim' => '2099-11-05',
            'mesmo_local' => '1',
            '_return' => '/',
            'website' => '',
        ]);
        $this->assertContains($r['code'], [302, 303]);
        $this->assertStringContainsString('lead=1', (string) $r['location']);
    }

    public function testLoginInvalidCredentialsStaysOnLogin(): void
    {
        $page = $this->client->get('/login');
        $csrf = $this->client->extractCsrf($page['body']);
        $this->assertNotNull($csrf);

        $r = $this->client->post('/login', [
            '_csrf' => $csrf,
            'email' => 'owner@titaniumrental.com',
            'password' => 'wrong-password',
            'privacy_accept' => '1',
        ]);
        $this->assertContains($r['code'], [302, 303]);
        $this->assertStringContainsString('/login', (string) $r['location']);
    }

    public function testLoginOwnerRedirectsAfterAuth(): void
    {
        $page = $this->client->get('/login');
        $csrf = $this->client->extractCsrf($page['body']);
        $this->assertNotNull($csrf);

        $r = $this->client->post('/login', [
            '_csrf' => $csrf,
            'email' => 'owner@titaniumrental.com',
            'password' => 'password123',
            'privacy_accept' => '1',
        ]);
        $this->assertContains($r['code'], [302, 303]);
        $location = (string) $r['location'];
        $this->assertTrue(
            str_contains($location, '/dashboard')
            || str_contains($location, '/account/password')
            || str_contains($location, '/login/2fa'),
            'Expected post-login redirect, got: ' . $location
        );
    }

    public function testCheckInRequiresAuthentication(): void
    {
        $r = $this->client->post('/reservations/1/checkin', [
            '_csrf' => 'invalid',
            'pickup_mileage' => '10000',
            'fuel_level_pickup' => 'full',
        ]);
        $this->assertContains($r['code'], [302, 303]);
        $this->assertStringContainsString('/login', (string) $r['location']);
    }
}
