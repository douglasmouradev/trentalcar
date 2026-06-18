<?php

declare(strict_types=1);

final class SecureImage
{
    /**
     * Re-encoda imagem para eliminar metadados/conteúdo malicioso embutido.
     *
     * @return string|null Caminho do ficheiro re-encodado ou null se GD indisponível
     */
    public static function reencode(string $sourcePath, string $mime, string $destPath): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if ($image === false) {
            return null;
        }

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, $destPath, 85),
            'image/png' => imagepng($image, $destPath, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $destPath, 85) : false,
            default => false,
        };

        imagedestroy($image);

        return $ok ? $destPath : null;
    }
}
