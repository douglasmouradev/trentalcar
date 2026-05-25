<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MailTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = BASE_PATH . '/storage/logs/mail.log';
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
        $_ENV['MAIL_DRIVER'] = 'log';
    }

    public function testLogDriverWritesMailFile(): void
    {
        $this->assertTrue(Mail::send('notify@example.com', 'Test subject', "Body line\n"));
        $this->assertFileExists($this->logFile);
        $content = file_get_contents($this->logFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('notify@example.com', $content);
        $this->assertStringContainsString('Test subject', $content);
        $this->assertStringContainsString('Body line', $content);
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->assertFalse(Mail::send('not-an-email', 'X', 'Y'));
        $this->assertFileDoesNotExist($this->logFile);
    }
}
