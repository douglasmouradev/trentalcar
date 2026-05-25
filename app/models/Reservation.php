<?php

declare(strict_types=1);

final class Reservation
{
    private const BLOCKING = "('pending','confirmed','active')";

    public static function find(int $id): ?array
    {
        $sql = 'SELECT r.*, c.full_name AS customer_name, c.document AS customer_document,
                car.brand, car.model, car.license_plate, car.color_hex,
                u.name AS operator_name,
                pl.name AS pickup_location_name, rl.name AS return_location_name
                FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id
                JOIN users u ON u.id = r.operator_id
                JOIN locations pl ON pl.id = r.pickup_location_id
                JOIN locations rl ON rl.id = r.return_location_id
                WHERE r.id = ?';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array{status:string,q:string,from:string,to:string} */
    public static function filtersFromQuery(): array
    {
        return [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'q' => trim((string) ($_GET['q'] ?? '')),
            'from' => trim((string) ($_GET['from'] ?? '')),
            'to' => trim((string) ($_GET['to'] ?? '')),
        ];
    }

    /**
     * @param array{status?:string,q?:string,from?:string,to?:string} $filters
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function forOperatorPaginated(?int $operatorId, int $page, int $perPage, array $filters = []): array
    {
        $base = ' FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id';
        $where = [];
        $params = [];
        if ($operatorId !== null) {
            $where[] = 'r.operator_id = ?';
            $params[] = $operatorId;
        }
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && in_array($status, ['pending', 'confirmed', 'active', 'completed', 'cancelled'], true)) {
            $where[] = 'r.status = ?';
            $params[] = $status;
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(r.code LIKE ? OR c.full_name LIKE ? OR car.license_plate LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $from = trim((string) ($filters['from'] ?? ''));
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[] = 'r.pickup_date >= ?';
            $params[] = $from;
        }
        $to = trim((string) ($filters['to'] ?? ''));
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[] = 'r.pickup_date <= ?';
            $params[] = $to;
        }
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $stmt = Database::pdo()->prepare('SELECT COUNT(*)' . $base . $whereSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate, car.color_hex'
            . $base . $whereSql . ' ORDER BY r.pickup_date DESC, r.id DESC LIMIT ' . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    /**
     * @param array{status?:string,q?:string,from?:string,to?:string} $filters
     * @return array<int, array<string, mixed>>
     */
    public static function exportRows(?int $operatorId, array $filters = []): array
    {
        $base = ' FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id';
        $where = [];
        $params = [];
        if ($operatorId !== null) {
            $where[] = 'r.operator_id = ?';
            $params[] = $operatorId;
        }
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && in_array($status, ['pending', 'confirmed', 'active', 'completed', 'cancelled'], true)) {
            $where[] = 'r.status = ?';
            $params[] = $status;
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(r.code LIKE ? OR c.full_name LIKE ? OR car.license_plate LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $from = trim((string) ($filters['from'] ?? ''));
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[] = 'r.pickup_date >= ?';
            $params[] = $from;
        }
        $to = trim((string) ($filters['to'] ?? ''));
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[] = 'r.pickup_date <= ?';
            $params[] = $to;
        }
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $sql = 'SELECT r.code, c.full_name AS customer_name, car.brand, car.model, car.license_plate,
                r.pickup_date, r.pickup_time, r.return_date, r.return_time, r.status, r.final_amount, r.payment_status'
            . $base . $whereSql . ' ORDER BY r.pickup_date DESC, r.id DESC LIMIT 5000';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function nextCode(): string
    {
        return self::nextCodeLocked(Database::pdo());
    }

    private static function nextCodeLocked(PDO $pdo): string
    {
        $year = (int) date('Y');
        $prefix = 'TRC-' . $year . '-';
        $stmt = $pdo->prepare(
            "SELECT code FROM reservations WHERE code LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();
        $n = 1;
        if (is_string($last) && preg_match('/TRC-\d+-(\d+)/', $last, $m)) {
            $n = (int) $m[1] + 1;
        }
        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica sobreposição de intervalos para o mesmo veículo.
     */
    public static function hasConflict(
        int $carId,
        string $pickupDate,
        string $pickupTime,
        string $returnDate,
        string $returnTime,
        ?int $excludeReservationId = null
    ): bool {
        $pickup = $pickupDate . ' ' . $pickupTime;
        $ret = $returnDate . ' ' . $returnTime;
        $sql = "SELECT COUNT(*) FROM reservations
                WHERE car_id = ? AND status IN " . self::BLOCKING . "
                AND CONCAT(pickup_date, ' ', pickup_time) < ?
                AND CONCAT(return_date, ' ', return_time) > ?";
        $params = [$carId, $ret, $pickup];
        if ($excludeReservationId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeReservationId;
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO reservations (code, customer_id, car_id, operator_id, pickup_location_id, return_location_id,
             pickup_date, pickup_time, return_date, return_time, daily_rate, total_days, total_amount, discount, final_amount,
             status, payment_status, payment_method, notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['code'], $d['customer_id'], $d['car_id'], $d['operator_id'], $d['pickup_location_id'], $d['return_location_id'],
            $d['pickup_date'], $d['pickup_time'], $d['return_date'], $d['return_time'],
            $d['daily_rate'], $d['total_days'], $d['total_amount'], $d['discount'], $d['final_amount'],
            $d['status'], $d['payment_status'], $d['payment_method'] ?? null, $d['notes'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * Cria reserva com verificação atómica de conflito (evita race condition).
     *
     * @return int|false ID criado ou false se houver conflito / carro indisponível
     */
    public static function createAtomic(array $d): int|false
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $lock = $pdo->prepare('SELECT id FROM cars WHERE id = ? AND deleted_at IS NULL FOR UPDATE');
            $lock->execute([$d['car_id']]);
            if (!$lock->fetch()) {
                $pdo->rollBack();
                return false;
            }
            if (self::hasConflict(
                $d['car_id'],
                $d['pickup_date'],
                $d['pickup_time'],
                $d['return_date'],
                $d['return_time'],
                null
            )) {
                $pdo->rollBack();
                return false;
            }
            if (empty($d['code'])) {
                $d['code'] = self::nextCodeLocked($pdo);
            }
            $id = self::create($d);
            self::refreshCarAvailability($pdo, (int) $d['car_id']);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return bool false se houver conflito
     */
    public static function updateAtomic(int $id, array $d): bool
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $prev = $pdo->prepare('SELECT car_id FROM reservations WHERE id = ? FOR UPDATE');
            $prev->execute([$id]);
            $prevRow = $prev->fetch();
            $oldCarId = $prevRow ? (int) $prevRow['car_id'] : (int) $d['car_id'];

            $lock = $pdo->prepare('SELECT id FROM cars WHERE id = ? AND deleted_at IS NULL FOR UPDATE');
            $lock->execute([$d['car_id']]);
            if (!$lock->fetch()) {
                $pdo->rollBack();
                return false;
            }
            if (self::hasConflict(
                $d['car_id'],
                $d['pickup_date'],
                $d['pickup_time'],
                $d['return_date'],
                $d['return_time'],
                $id
            )) {
                $pdo->rollBack();
                return false;
            }
            self::update($id, $d);
            self::refreshCarAvailability($pdo, (int) $d['car_id']);
            if ($oldCarId !== (int) $d['car_id']) {
                self::refreshCarAvailability($pdo, $oldCarId);
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return true|string true em sucesso ou chave de erro Lang
     */
    public static function transition(int $id, string $action): true|string
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $r = $stmt->fetch();
            if (!$r) {
                $pdo->rollBack();
                return 'error.404_title';
            }
            $status = (string) $r['status'];
            $carId = (int) $r['car_id'];
            $newStatus = match ($action) {
                'confirm' => $status === 'pending' ? 'confirmed' : null,
                'activate' => $status === 'confirmed' ? 'active' : null,
                'complete' => $status === 'active' ? 'completed' : null,
                'cancel' => in_array($status, ['pending', 'confirmed', 'active'], true) ? 'cancelled' : null,
                default => null,
            };
            if ($newStatus === null) {
                $pdo->rollBack();
                return 'reservation.invalid_transition';
            }
            if ($newStatus === 'completed') {
                $upd = $pdo->prepare(
                    'UPDATE reservations SET status = ?, actual_return_at = NOW() WHERE id = ?'
                );
                $upd->execute([$newStatus, $id]);
            } else {
                $upd = $pdo->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $upd->execute([$newStatus, $id]);
            }
            self::refreshCarAvailability($pdo, $carId);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function refreshCarAvailability(PDO $pdo, int $carId): void
    {
        $carStmt = $pdo->prepare('SELECT status FROM cars WHERE id = ? AND deleted_at IS NULL');
        $carStmt->execute([$carId]);
        $car = $carStmt->fetch();
        if (!$car) {
            return;
        }
        $current = (string) $car['status'];
        if (in_array($current, ['maintenance', 'inactive'], true)) {
            return;
        }
        $cntStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM reservations WHERE car_id = ? AND status = 'active'"
        );
        $cntStmt->execute([$carId]);
        $active = (int) $cntStmt->fetchColumn();
        $newStatus = $active > 0 ? 'rented' : 'available';
        $upd = $pdo->prepare('UPDATE cars SET status = ? WHERE id = ?');
        $upd->execute([$newStatus, $carId]);
    }

    public static function update(int $id, array $d): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE reservations SET customer_id=?, car_id=?, pickup_location_id=?, return_location_id=?,
             pickup_date=?, pickup_time=?, return_date=?, return_time=?, daily_rate=?, total_days=?, total_amount=?, discount=?, final_amount=?,
             status=?, payment_status=?, payment_method=?, notes=? WHERE id=?'
        );
        $stmt->execute([
            $d['customer_id'], $d['car_id'], $d['pickup_location_id'], $d['return_location_id'],
            $d['pickup_date'], $d['pickup_time'], $d['return_date'], $d['return_time'],
            $d['daily_rate'], $d['total_days'], $d['total_amount'], $d['discount'], $d['final_amount'],
            $d['status'], $d['payment_status'], $d['payment_method'] ?? null, $d['notes'] ?? null, $id,
        ]);
    }

    /** @deprecated Use transition() */
    public static function setStatus(int $id, string $status): void
    {
        if ($status === 'cancelled') {
            self::transition($id, 'cancel');
            return;
        }
        $stmt = Database::pdo()->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    /** Calendar events in range */
    public static function eventsBetween(string $start, string $end, ?int $carId, ?int $operatorId, ?string $status): array
    {
        $sql = 'SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate, car.color_hex,
                u.name AS operator_name,
                pl.name AS pickup_location_name, rl.name AS return_location_name
                FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id
                JOIN users u ON u.id = r.operator_id
                JOIN locations pl ON pl.id = r.pickup_location_id
                JOIN locations rl ON rl.id = r.return_location_id
                WHERE r.return_date >= ? AND r.pickup_date <= ?';
        $params = [$start, $end];
        if ($carId) {
            $sql .= ' AND r.car_id = ?';
            $params[] = $carId;
        }
        if ($operatorId) {
            $sql .= ' AND r.operator_id = ?';
            $params[] = $operatorId;
        }
        if ($status !== null && $status !== '') {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY r.pickup_date, r.pickup_time';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
