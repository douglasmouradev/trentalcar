<?php

declare(strict_types=1);

/** Operações atómicas em ficheiros JSON para rate limiting. */
final class FileRateStore
{
    /** @return array<string, mixed>|null */
    public static function read(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return null;
        }
        flock($fh, LOCK_SH);
        $raw = stream_get_contents($fh) ?: '';
        flock($fh, LOCK_UN);
        fclose($fh);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /** @param array<string, mixed> $data */
    public static function write(string $path, array $data): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fh = fopen($path, 'c+');
        if ($fh === false) {
            return;
        }
        flock($fh, LOCK_EX);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($data, JSON_THROW_ON_ERROR));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
     * @param callable(array<string, mixed>|null): array<string, mixed> $mutator
     * @return array<string, mixed>
     */
    public static function mutate(string $path, callable $mutator): array
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fh = fopen($path, 'c+');
        if ($fh === false) {
            return $mutator(null);
        }
        flock($fh, LOCK_EX);
        $raw = stream_get_contents($fh) ?: '';
        $current = json_decode($raw, true);
        $current = is_array($current) ? $current : null;
        $next = $mutator($current);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($next, JSON_THROW_ON_ERROR));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
        return $next;
    }
}
