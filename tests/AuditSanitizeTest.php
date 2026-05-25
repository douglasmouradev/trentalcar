<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuditSanitizeTest extends TestCase
{
    public function testRedactsSensitiveFields(): void
    {
        $out = Audit::sanitize([
            'email' => 'a@b.com',
            'document' => '123',
            'attachment_path' => '/secret/file.pdf',
            'status' => 'active',
        ]);
        $this->assertSame('[redacted]', $out['email']);
        $this->assertSame('[redacted]', $out['document']);
        $this->assertSame('[file]', $out['attachment_path']);
        $this->assertSame('active', $out['status']);
    }
}
