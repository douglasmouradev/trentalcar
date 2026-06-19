<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecurityTxtTest extends TestCase
{
    public function testSecurityTxtOutput(): void
    {
        $_ENV['SECURITY_CONTACT_EMAIL'] = 'sec@example.com';
        ob_start();
        (new SecurityTxtController())->index();
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('Contact: mailto:sec@example.com', $out);
        $this->assertStringContainsString('Expires:', $out);
    }
}
