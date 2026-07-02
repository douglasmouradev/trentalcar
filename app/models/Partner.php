<?php

declare(strict_types=1);

final class Partner
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        $stmt = Database::query(
            "SELECT id, name, email, phone, is_active, lang_pref, created_at
             FROM users WHERE role = 'partner' ORDER BY name"
        );
        return $stmt->fetchAll();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginated(int $page, int $perPage): array
    {
        $total = (int) Database::query("SELECT COUNT(*) FROM users WHERE role = 'partner'")->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $stmt = Database::prepare(
            "SELECT id, name, email, phone, is_active, lang_pref, created_at
             FROM users WHERE role = 'partner'
             ORDER BY name
             LIMIT " . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset']
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if ($rows !== []) {
            $ids = array_map(static fn (array $row): int => (int) $row['id'], $rows);
            $counts = UserCar::carCountsForUsers($ids);
            foreach ($rows as &$row) {
                $row['car_count'] = $counts[(int) $row['id']] ?? 0;
            }
            unset($row);
        }
        return [
            'rows' => $rows,
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::prepare(
            "SELECT * FROM users WHERE id = ? AND role = 'partner' LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
