<?php

declare(strict_types=1);

/** Valida e grava anexos de clientes (PDF e imagens re-encodadas). */
final class CustomerAttachment
{
    /**
     * @param array<string, mixed>|null $file
     * @return string|null false em falha
     */
    public static function store(?array $file, ?string $existingName): string|null|false
    {
        if (empty($file) || empty($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
            return $existingName;
        }

        $tmp = (string) $file['tmp_name'];
        $max = (int) (Config::app()['max_upload'] ?? 5242880);
        if (($file['size'] ?? 0) > $max) {
            Flash::error(Lang::get('upload.too_large'));
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            Flash::error(Lang::get('upload.invalid_type'));
            return false;
        }
        if (str_starts_with($mime, 'image/')) {
            if (!ImageUpload::magicMatches($tmp, $mime)) {
                Flash::error(Lang::get('upload.invalid_type'));
                return false;
            }
        } elseif (!self::magicMatchesPdf($tmp)) {
            Flash::error(Lang::get('upload.invalid_type'));
            return false;
        }

        $dir = BASE_PATH . '/storage/customers';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = 'cust_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        $dest = $dir . '/' . $name;

        if (str_starts_with($mime, 'image/')) {
            $encoded = ImageUpload::saveFromTmp($tmp, $dest, $mime);
            if (!$encoded) {
                Flash::error(Lang::get('upload.failed'));
                return false;
            }
        } else {
            if (!move_uploaded_file($tmp, $dest)) {
                Flash::error(Lang::get('upload.failed'));
                return false;
            }
        }

        if ($existingName !== null && $existingName !== '') {
            $oldFs = self::resolvePath($existingName);
            if ($oldFs !== null && is_file($oldFs)) {
                @unlink($oldFs);
            }
        }

        return $name;
    }

    public static function resolvePath(string $stored): ?string
    {
        $name = basename(parse_url($stored, PHP_URL_PATH) ?: $stored);
        if (str_starts_with($stored, 'storage/customers/')) {
            $name = basename($stored);
        }
        if (!preg_match('/^cust_[a-f0-9]{16}\.(pdf|jpe?g|png|webp)$/i', $name)) {
            return null;
        }
        $base = realpath(BASE_PATH . '/storage/customers');
        if ($base === false) {
            return null;
        }
        $full = realpath($base . DIRECTORY_SEPARATOR . $name);
        if ($full === false || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $full;
    }

    public static function storeRelative(string $filename): string
    {
        return 'storage/customers/' . basename($filename);
    }

    public static function filesystemPath(string $stored): ?string
    {
        if (str_starts_with($stored, 'storage/customers/')) {
            $full = BASE_PATH . '/' . $stored;
            return is_file($full) ? $full : null;
        }
        $name = basename(parse_url($stored, PHP_URL_PATH) ?: $stored);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $name)) {
            return null;
        }
        $base = realpath(BASE_PATH . '/storage/customers');
        if ($base === false) {
            return null;
        }
        $full = realpath($base . DIRECTORY_SEPARATOR . $name);
        if ($full === false || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $full;
    }

    private static function magicMatchesPdf(string $path): bool
    {
        $head = @file_get_contents($path, false, null, 0, 4);

        return $head !== false && str_starts_with($head, '%PDF');
    }
}
