<?php

declare(strict_types=1);

final class CsvResponse
{
    /**
     * @param list<string> $headers
     * @param iterable<int, list<string|int|float|null>> $rows
     */
    public static function download(string $filename, array $headers, iterable $rows, string $delimiter = ';'): void
    {
        $safeName = str_replace(['"', "\r", "\n"], '', $filename);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, $delimiter);
        foreach ($rows as $row) {
            $line = [];
            foreach ($row as $cell) {
                $line[] = $cell === null ? '' : (string) $cell;
            }
            fputcsv($out, $line, $delimiter);
        }
        fclose($out);
    }
}
