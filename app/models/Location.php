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

    /** @return array<string, mixed>|null */
    public static function findByName(string $name): ?array
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $stmt = Database::prepare('SELECT * FROM locations WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Garante os locais do site (MIA / MCO / hotel) ativos no cadastro.
     * Resolve dropdown vazio em Nova reserva quando a tabela ainda não foi populada.
     */
    public static function ensurePickupDefaults(): void
    {
        $defaults = [
            'Aeroporto MIA' => ['address' => 'Miami International Airport', 'city' => 'Miami', 'state' => 'FL', 'zip_code' => '33126'],
            'Aeroporto MCO' => ['address' => 'Orlando International Airport', 'city' => 'Orlando', 'state' => 'FL', 'zip_code' => '32827'],
            'Entrega no hotel' => ['address' => 'Entrega sob demanda (hotel)', 'city' => 'Orlando', 'state' => 'FL', 'zip_code' => null],
        ];
        foreach ($defaults as $name => $meta) {
            $existing = self::findByName($name);
            if ($existing === null) {
                self::create([
                    'name' => $name,
                    'address' => $meta['address'],
                    'city' => $meta['city'],
                    'state' => $meta['state'],
                    'zip_code' => $meta['zip_code'],
                    'phone' => null,
                    'is_active' => 1,
                ]);
                continue;
            }
            if ((int) ($existing['is_active'] ?? 0) !== 1) {
                self::update((int) $existing['id'], [
                    'name' => $name,
                    'address' => (string) ($existing['address'] ?? $meta['address']),
                    'city' => (string) ($existing['city'] ?? $meta['city']),
                    'state' => (string) ($existing['state'] ?? $meta['state']),
                    'zip_code' => $existing['zip_code'] ?? $meta['zip_code'],
                    'phone' => $existing['phone'] ?? null,
                    'is_active' => 1,
                ]);
            }
        }
    }

    /** Resolve texto do lead (ex.: "Entrega no hotel — Hilton") para id de local ativo. */
    public static function resolveIdFromLeadLocal(?string $local): ?int
    {
        $local = trim((string) $local);
        if ($local === '') {
            return null;
        }
        $base = preg_replace('/\s+[—\-]\s+.+$/u', '', $local) ?? $local;
        $base = trim($base);
        $row = self::findByName($base);
        if ($row !== null && (int) ($row['is_active'] ?? 0) === 1) {
            return (int) $row['id'];
        }
        return null;
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
