<?php

declare(strict_types=1);

final class FixedCost
{
    /** @return list<string> */
    public static function fields(): array
    {
        return [
            'site_rent',
            'internet',
            'water',
            'electricity',
            'phone',
            'staff',
            'extra',
        ];
    }

    /** @return array<string, mixed> */
    public static function get(): array
    {
        if (!Schema::hasTable('fixed_costs')) {
            return self::defaults();
        }
        self::ensureRow();
        $stmt = Database::query('SELECT * FROM fixed_costs WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        return is_array($row) ? $row : self::defaults();
    }

    /** @param array<string, float|int|string> $amounts */
    public static function update(array $amounts): void
    {
        if (!Schema::hasTable('fixed_costs')) {
            return;
        }
        self::ensureRow();
        $set = implode(', ', array_map(static fn (string $f): string => "{$f}=?", self::fields()));
        $params = [];
        foreach (self::fields() as $field) {
            $params[] = max(0.0, (float) str_replace(',', '.', (string) ($amounts[$field] ?? '0')));
        }
        $stmt = Database::prepare("UPDATE fixed_costs SET {$set} WHERE id = 1");
        $stmt->execute($params);
    }

    public static function total(?array $row = null): float
    {
        $row ??= self::get();
        $sum = 0.0;
        foreach (self::fields() as $field) {
            $sum += max(0.0, (float) ($row[$field] ?? 0));
        }
        return round($sum, 2);
    }

    /** @return array<string, float> */
    public static function defaults(): array
    {
        $out = ['id' => 1];
        foreach (self::fields() as $field) {
            $out[$field] = 0.0;
        }
        return $out;
    }

    private static function ensureRow(): void
    {
        if (!Schema::hasTable('fixed_costs')) {
            return;
        }
        $exists = (int) Database::query('SELECT COUNT(*) FROM fixed_costs WHERE id = 1')->fetchColumn();
        if ($exists === 0) {
            Database::prepare(
                'INSERT INTO fixed_costs (id, site_rent, internet, water, electricity, phone, staff, extra) VALUES (1,0,0,0,0,0,0,0)'
            )->execute();
        }
    }
}
