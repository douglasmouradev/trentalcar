<?php

declare(strict_types=1);

final class Location
{
    /** @return array<int, array<string, mixed>> */
    public static function allActive(): array
    {
        $stmt = Database::query('SELECT * FROM locations WHERE is_active = 1 ORDER BY name');
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::query('SELECT * FROM locations ORDER BY name')->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::prepare('SELECT * FROM locations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function isActive(int $id): bool
    {
        $loc = self::find($id);
        return $loc !== null && (int) ($loc['is_active'] ?? 0) === 1;
    }

    public static function findActive(int $id): bool
    {
        return self::isActive($id);
    }

    /** @param array<string, mixed> $d */
    public static function create(array $d): int
    {
        $stmt = Database::prepare(
            'INSERT INTO locations (name, address, city, state, zip_code, phone, is_active) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['name'], $d['address'], $d['city'], $d['state'],
            $d['zip_code'] ?? null, $d['phone'] ?? null, (int) ($d['is_active'] ?? 1),
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $d */
    public static function update(int $id, array $d): void
    {
        $stmt = Database::prepare(
            'UPDATE locations SET name=?, address=?, city=?, state=?, zip_code=?, phone=?, is_active=? WHERE id=?'
        );
        $stmt->execute([
            $d['name'], $d['address'], $d['city'], $d['state'],
            $d['zip_code'] ?? null, $d['phone'] ?? null, (int) ($d['is_active'] ?? 1), $id,
        ]);
    }
}
