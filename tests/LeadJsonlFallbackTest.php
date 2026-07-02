<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LeadJsonlFallbackTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = BASE_PATH . '/storage/leads';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        $file = $this->dir . '/leads.jsonl';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testAppendWithoutEncryptionStoresHashesOnly(): void
    {
        $prev = $_ENV['APP_KEY'] ?? null;
        $_ENV['APP_KEY'] = '';

        LeadJsonlFallback::append([
            'full_name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '11999990000',
        ]);

        if ($prev === null) {
            unset($_ENV['APP_KEY']);
        } else {
            $_ENV['APP_KEY'] = $prev;
        }

        $lines = file($this->dir . '/leads.jsonl', FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);
        /** @var array<string, mixed> $row */
        $row = json_decode((string) $lines[0], true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('enc', $row);
        $this->assertArrayHasKey('email_hash', $row);
        $this->assertStringNotContainsString('user@example.com', (string) $lines[0]);
    }

    public function testPurgeExpiredRemovesOldLines(): void
    {
        $file = $this->dir . '/leads.jsonl';
        $old = json_encode(['at' => gmdate('c', time() - 86400 * 10)], JSON_THROW_ON_ERROR);
        $new = json_encode(['at' => gmdate('c')], JSON_THROW_ON_ERROR);
        file_put_contents($file, $old . "\n" . $new . "\n");

        $removed = LeadJsonlFallback::purgeExpired();
        $this->assertSame(1, $removed);
        $remaining = file($file, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($remaining);
        $this->assertCount(1, $remaining);
    }
}
