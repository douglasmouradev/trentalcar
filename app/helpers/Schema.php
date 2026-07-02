<?php

declare(strict_types=1);

final class Schema
{
    /** @var array<string, bool> */
    private static array $columns = [];

    public static function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, self::$columns)) {
            return self::$columns[$key];
        }
        try {
            $stmt = Database::prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->execute([$table, $column]);
            self::$columns[$key] = (int) $stmt->fetchColumn() > 0;
        } catch (Throwable) {
            self::$columns[$key] = false;
        }
        return self::$columns[$key];
    }

    public static function hasTable(string $table): bool
    {
        $key = 'table:' . $table;
        if (array_key_exists($key, self::$columns)) {
            return self::$columns[$key];
        }
        try {
            $stmt = Database::prepare(
                'SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $stmt->execute([$table]);
            self::$columns[$key] = (int) $stmt->fetchColumn() > 0;
        } catch (Throwable) {
            self::$columns[$key] = false;
        }
        return self::$columns[$key];
    }
}
