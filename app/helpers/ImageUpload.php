<?php

declare(strict_types=1);

/** Validação e gravação segura de imagens (magic bytes + re-encode via GD). */
final class ImageUpload
{
    /** @var array<string, string> */
    public const MIME_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function detectMime(string $tmpPath): ?string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath) ?: '';
        if (!isset(self::MIME_EXT[$mime]) || !self::magicMatches($tmpPath, $mime)) {
            return null;
        }

        return $mime;
    }

    public static function magicMatches(string $path, string $mime): bool
    {
        $head = @file_get_contents($path, false, null, 0, 12);
        if ($head === false || $head === '') {
            return false;
        }

        return match ($mime) {
            'image/jpeg' => str_starts_with($head, "\xFF\xD8\xFF"),
            'image/png' => str_starts_with($head, "\x89PNG\r\n\x1a\n"),
            'image/webp' => str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP',
            default => false,
        };
    }

    /** Grava imagem re-encodada; nunca copia o ficheiro original em bruto. */
    public static function saveFromTmp(string $tmpPath, string $destPath, string $mime): bool
    {
        return SecureImage::reencode($tmpPath, $mime, $destPath) !== null;
    }
}
