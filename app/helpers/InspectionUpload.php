<?php

declare(strict_types=1);

final class InspectionUpload
{
    public static function storeDir(): string
    {
        return BASE_PATH . '/storage/inspections';
    }

    /** @param array<string, mixed>|null $file */
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
        $tmp = (string) $file['tmp_name'];
        $mime = ImageUpload::detectMime($tmp);
        if ($mime === null) {
            throw new InvalidArgumentException('invalid mime');
        }
        $dir = self::storeDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = sprintf('r%d-%s-%s.%s', $reservationId, $kind, bin2hex(random_bytes(6)), ImageUpload::MIME_EXT[$mime]);
        $dest = $dir . '/' . $name;
        if (!ImageUpload::saveFromTmp($tmp, $dest, $mime)) {
            throw new RuntimeException('save failed');
        }

        return $name;
    }

    public static function deleteStored(?string $stored): void
    {
        if ($stored === null || $stored === '') {
            return;
        }
        $path = self::storeDir() . '/' . basename($stored);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public static function absolutePath(int $reservationId, string $stored): ?string
    {
        $base = basename($stored);
        if (!preg_match('/^r' . $reservationId . '-/', $base)) {
            return null;
        }
        $storage = self::storeDir() . '/' . $base;
        if (is_file($storage)) {
            return $storage;
        }
        $legacy = BASE_PATH . '/public/assets/uploads/' . ltrim($stored, '/');
        return is_file($legacy) ? $legacy : null;
    }

    public static function url(int $reservationId, string $stored): string
    {
        return Router::url('/reservations/' . $reservationId . '/inspection-photo?f=' . rawurlencode(basename($stored)));
    }
}
