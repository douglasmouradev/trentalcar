<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MailTest extends TestCase
{
    private string $mailDir;

    protected function setUp(): void
    {
        $this->mailDir = BASE_PATH . '/storage/mail';
        if (is_dir($this->mailDir)) {
            foreach (glob($this->mailDir . '/*.eml') ?: [] as $file) {
                unlink($file);
            }
        }
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['MAIL_SMTP_HOST'] = '';
    }

    public function testDevDriverStoresEmlFile(): void
    {
        $this->assertTrue(Mail::send('notify@example.com', 'Test subject', "Body line\n"));
        $files = glob($this->mailDir . '/*.eml');
        $this->assertIsArray($files);
        $this->assertCount(1, $files);
        $content = file_get_contents($files[0]);
        $this->assertIsString($content);
        $this->assertStringContainsString('notify@example.com', $content);
        $this->assertStringContainsString('Test subject', $content);
        $this->assertStringContainsString('Body line', $content);
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->assertFalse(Mail::send('not-an-email', 'X', 'Y'));
        $files = glob($this->mailDir . '/*.eml') ?: [];
        $this->assertSame([], $files);
    }
}
