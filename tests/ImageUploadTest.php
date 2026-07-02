<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ImageUploadTest extends TestCase
{
    public function testMagicMatchesRejectsPdfMasqueradingAsJpeg(): void
    {
        $dir = sys_get_temp_dir();
        $path = $dir . '/fake-jpeg-' . bin2hex(random_bytes(4)) . '.bin';
        file_put_contents($path, '%PDF-1.4 fake');
        try {
            $this->assertFalse(ImageUpload::magicMatches($path, 'image/jpeg'));
        } finally {
            @unlink($path);
        }
    }

    public function testDetectMimeRejectsInvalidContent(): void
    {
        $dir = sys_get_temp_dir();
        $path = $dir . '/not-image-' . bin2hex(random_bytes(4)) . '.bin';
        file_put_contents($path, 'plain text');
        try {
            $this->assertNull(ImageUpload::detectMime($path));
        } finally {
            @unlink($path);
        }
    }

    public function testMimeExtMapCoversExpectedTypes(): void
    {
        $this->assertArrayHasKey('image/jpeg', ImageUpload::MIME_EXT);
        $this->assertArrayHasKey('image/png', ImageUpload::MIME_EXT);
        $this->assertArrayHasKey('image/webp', ImageUpload::MIME_EXT);
    }
}
