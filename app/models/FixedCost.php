<?php

declare(strict_types=1);

final class FixedCost
{
    /**
     * Mesmas categorias do custo mensal por veículo (ordem da UI).
     *
     * @return list<string>
     */
    public static function fields(): array
    {
        return [
            'insurance',
            'document',
            'plate',
            'wash',
            'site_rent',
            'internet',
            'water',
            'electricity',
            'phone',
            'staff',
            'tag_annual',
            'fuel',
            'toll',
            'maintenance',
            'extra',
        ];
    }

    /** Chave i18n alinhada aos labels do custo mensal. */
    public static function langKey(string $field): string
    {
        return match ($field) {
            'insurance' => 'car.monthly_insurance',
            'document' => 'car.monthly_document',
            'plate' => 'car.monthly_ipva',
            'wash' => 'car.monthly_wash',
            'site_rent' => 'car.monthly_site_rent',
            'internet' => 'car.monthly_internet',
            'water' => 'car.monthly_water',
            'electricity' => 'car.monthly_electricity',
            'phone' => 'car.monthly_phone',
            'staff' => 'car.monthly_staff',
            'tag_annual' => 'car.monthly_tag_annual',
            'fuel' => 'car.monthly_fuel',
            'toll' => 'car.monthly_toll',
            'maintenance' => 'car.monthly_maintenance',
            'extra' => 'car.monthly_extra',
            default => 'fixed_costs.' . $field,
        };
    }

    /** Campos existentes na tabela (após migrations). */
    public static function availableFields(): array
    {
        if (!Schema::hasTable('fixed_costs')) {
            return self::fields();
        }
        return array_values(array_filter(
            self::fields(),
            static fn (string $f): bool => Schema::hasColumn('fixed_costs', $f)
        ));
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
        $writable = array_values(array_filter(
            self::fields(),
            static fn (string $f): bool => Schema::hasColumn('fixed_costs', $f)
        ));
        if ($writable === []) {
            return;
        }
        $set = implode(', ', array_map(static fn (string $f): string => "{$f}=?", $writable));
        $params = [];
        foreach ($writable as $field) {
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

    /** @return array<string, float|int> */
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
            Database::prepare('INSERT INTO fixed_costs (id) VALUES (1)')->execute();
        }
    }
}
