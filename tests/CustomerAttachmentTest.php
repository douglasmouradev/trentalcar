<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CustomerAttachmentTest extends TestCase
{
    public function testStoreRelative(): void
    {
        self::assertSame('storage/customers/cust_abc.pdf', CustomerAttachment::storeRelative('cust_abc.pdf'));
    }

    public function testFilesystemPathFromRelative(): void
    {
        $dir = BASE_PATH . '/storage/customers';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'test_' . bin2hex(random_bytes(4)) . '.txt';
        $rel = CustomerAttachment::storeRelative($name);
        file_put_contents(BASE_PATH . '/' . $rel, 'ok');
        self::assertSame(BASE_PATH . '/' . $rel, CustomerAttachment::filesystemPath($rel));
        unlink(BASE_PATH . '/' . $rel);
    }

    public function testFilesystemPathLegacyUrl(): void
    {
        $dir = BASE_PATH . '/storage/customers';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'legacy_' . bin2hex(random_bytes(4)) . '.txt';
        file_put_contents($dir . '/' . $name, 'legacy');
        $legacy = 'http://localhost/app/storage/customers/' . $name;
        $expected = realpath($dir . DIRECTORY_SEPARATOR . $name);
        $actual = CustomerAttachment::filesystemPath($legacy);
        self::assertNotFalse($expected);
        self::assertSame($expected, $actual);
        unlink($dir . '/' . $name);
    }
}
