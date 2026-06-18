<?php

declare(strict_types=1);

final class InspectionUpload
{
    public static function store(?array $file, int $reservationId, string $kind): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new InvalidArgumentException('upload failed');
        }
        $max = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880);
        if ((int) ($file['size'] ?? 0) > $max) {
            throw new InvalidArgumentException('file too large');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file((string) $file['tmp_name']) ?: '';
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new InvalidArgumentException('invalid mime'),
        };
        $dir = BASE_PATH . '/public/assets/uploads/inspections';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = sprintf('r%d-%s-%s.%s', $reservationId, $kind, bin2hex(random_bytes(6)), $ext);
        $dest = $dir . '/' . $name;
        if (!self::reencodeAndSave((string) $file['tmp_name'], $dest, $mime)) {
            throw new RuntimeException('save failed');
        }
        return 'inspections/' . $name;
    }

    private static function reencodeAndSave(string $tmp, string $dest, string $mime): bool
    {
        if (!extension_loaded('gd')) {
            return move_uploaded_file($tmp, $dest);
        }
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
            default => false,
        };
        if ($img === false) {
            return move_uploaded_file($tmp, $dest);
        }
        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($img, $dest, 85),
            'image/png' => imagepng($img, $dest, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($img, $dest, 85) : false,
            default => false,
        };
        imagedestroy($img);
        return (bool) $ok;
    }
}
