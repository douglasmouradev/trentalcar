<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PublicLeadControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_URL'] = 'http://localhost';
        $_POST = [];
    }

    public function testValidDateAcceptsIsoFormatOnly(): void
    {
        $method = new ReflectionMethod(PublicLeadController::class, 'validDate');
        $this->assertTrue($method->invoke(null, '2026-06-01'));
        $this->assertFalse($method->invoke(null, '01-06-2026'));
        $this->assertFalse($method->invoke(null, '2026-13-40'));
        $this->assertFalse($method->invoke(null, ''));
    }

    public function testResolveReturnUrlBlocksExternalPaths(): void
    {
        $_POST['_return'] = '//evil.example/phish';
        $method = new ReflectionMethod(PublicLeadController::class, 'resolveReturnUrl');
        $this->assertSame(Router::url('/'), $method->invoke(null));

        $_POST['_return'] = '/reservar';
        $this->assertSame(Router::url('/reservar'), $method->invoke(null));
    }

    public function testCollectOldNormalizesAliasesAndTrim(): void
    {
        $_POST = [
            'full_name' => '  Maria ',
            'email' => 'maria@example.com',
            'phone' => '11999990000',
            'local' => ' Centro ',
            'inicio' => '2026-07-01',
            'fim' => '2026-07-05',
            'car_id' => '12',
        ];
        $method = new ReflectionMethod(PublicLeadController::class, 'collectOld');
        $old = $method->invoke(null);
        $this->assertSame('Maria', $old['nome']);
        $this->assertSame('maria@example.com', $old['email']);
        $this->assertSame('11999990000', $old['telefone']);
        $this->assertSame('Centro', $old['local']);
        $this->assertSame(12, $old['car_id']);
    }
}
