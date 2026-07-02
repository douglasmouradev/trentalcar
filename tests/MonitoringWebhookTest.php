<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MonitoringWebhookTest extends TestCase
{
    public function testSanitizeContextRedactsMailFields(): void
    {
        $out = MonitoringWebhook::sanitizeContext([
            'to' => 'client@example.com',
            'subject' => 'Your reservation',
            'id' => 42,
        ]);
        $this->assertSame('[redacted]', $out['to']);
        $this->assertSame('[redacted]', $out['subject']);
        $this->assertSame(42, $out['id']);
    }

    public function testSanitizeContextRedactsNestedAndScrubsErrors(): void
    {
        $out = MonitoringWebhook::sanitizeContext([
            'error' => 'SMTP failed for user@corp.com with password=abc123',
            'meta' => ['customer_email' => 'hidden@example.com', 'code' => 'E001'],
        ]);
        $this->assertStringNotContainsString('user@corp.com', (string) $out['error']);
        $this->assertStringNotContainsString('abc123', (string) $out['error']);
        $this->assertStringContainsString('[email]', (string) $out['error']);
        $this->assertSame('[redacted]', $out['meta']['customer_email']);
        $this->assertSame('E001', $out['meta']['code']);
    }
}
