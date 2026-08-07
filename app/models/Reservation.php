<?php

declare(strict_types=1);

final class Reservation
{
    private const BLOCKING = "('pending','confirmed','active')";

    /** @return array<string, mixed>|null */
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
        $stmt = Database::prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public static function findByCodeAndEmail(string $code, string $email): ?array
    {
        $code = strtoupper(trim($code));
        $email = trim($email);
        if ($code === '' || $email === '') {
            return null;
        }
        $sql = 'SELECT r.code, r.status, r.pickup_date, r.return_date, r.final_amount,
                c.full_name AS customer_name,
                car.brand, car.model,
                pl.name AS pickup_location_name, rl.name AS return_location_name
                FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id
                JOIN locations pl ON pl.id = r.pickup_location_id
                JOIN locations rl ON rl.id = r.return_location_id
                WHERE r.code = ? AND LOWER(c.email) = LOWER(?)
                LIMIT 1';
        $stmt = Database::prepare($sql);
        $stmt->execute([$code, $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function addExtraCharges(int $id, float $amount): void
    {
        self::addExtraChargesWithPdo(Database::pdo(), $id, $amount);
    }

    private static function addExtraChargesWithPdo(PDO $pdo, int $id, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        Database::prepare(
            'UPDATE reservations SET extra_charges = extra_charges + ?, final_amount = final_amount + ? WHERE id = ?'
        )->execute([$amount, $amount, $id]);
    }

    /** @param array{damage_notes?: ?string, extra_charges?: float, photo_path?: ?string, created_by?: ?int} $inspection */
    private static function insertInspectionWithPdo(
        PDO $pdo,
        int $reservationId,
        string $kind,
        int $mileage,
        string $fuelLevel,
        array $inspection,
    ): void {
        if (!Schema::hasTable('reservation_inspections')) {
            return;
        }
        $stmt = Database::prepare(
            'INSERT INTO reservation_inspections
             (reservation_id, kind, mileage, fuel_level, damage_notes, extra_charges, photo_path, created_by)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $reservationId,
            $kind,
            $mileage,
            $fuelLevel,
            $inspection['damage_notes'] ?? null,
            (float) ($inspection['extra_charges'] ?? 0),
            $inspection['photo_path'] ?? null,
            $inspection['created_by'] ?? null,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function forOperator(?int $operatorId): array
    {
        $sql = 'SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate, car.color_hex
                FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id';
        $params = [];
        if ($operatorId !== null) {
            $sql .= ' WHERE r.operator_id = ?';
            $params[] = $operatorId;
        }
        $sql .= ' ORDER BY r.pickup_date DESC, r.id DESC';
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{rows: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function forOperatorPaginated(
        ?int $operatorId,
        int $page,
        int $perPage,
        array $filters = []
    ): array {
        $base = ' FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN cars car ON car.id = r.car_id';
        $where = '';
        $params = [];
        if ($operatorId !== null) {
            $where = ' WHERE r.operator_id = ?';
            $params[] = $operatorId;
        } else {
            $where = ' WHERE 1=1';
        }
        if (!empty($filters['status'])) {
            $where .= ' AND r.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['payment_status'])) {
            $where .= ' AND r.payment_status = ?';
            $params[] = $filters['payment_status'];
        }
        if (!empty($filters['q'])) {
            $like = '%' . trim((string) $filters['q']) . '%';
            $where .= ' AND (r.code LIKE ? OR c.full_name LIKE ? OR car.license_plate LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND r.pickup_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND r.pickup_date <= ?';
            $params[] = $filters['date_to'];
        }
        $stmt = Database::prepare('SELECT COUNT(*)' . $base . $where);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $meta = Pagination::meta($total, $page, $perPage);
        $sql = 'SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate, car.color_hex'
            . $base . $where . ' ORDER BY r.pickup_date DESC, r.id DESC LIMIT ' . (int) $meta['perPage'] . ' OFFSET ' . (int) $meta['offset'];
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return [
            'rows' => $stmt->fetchAll(),
            'total' => $meta['total'],
            'page' => $meta['page'],
            'perPage' => $meta['perPage'],
            'totalPages' => $meta['totalPages'],
        ];
    }

    public static function nextCode(): string
    {
        $pdo = Database::pdo();
        return self::nextCodeLocked($pdo);
    }

    /**
     * Cria reserva com verificação de conflito e código numa transação.
     * @param array<string, mixed> $d
     */
    public static function createSafely(array $d, ?int $leadIdToConvert = null): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            self::lockCarForUpdate($pdo, (int) $d['car_id']);
            self::assertNoConflict(
                $pdo,
                (int) $d['car_id'],
                (string) $d['pickup_date'],
                (string) $d['pickup_time'],
                (string) $d['return_date'],
                (string) $d['return_time'],
                null
            );
            $d['code'] = self::nextCodeLocked($pdo);
            $id = self::insertWithPdo($pdo, $d);
            if ($leadIdToConvert !== null && $leadIdToConvert > 0) {
                Database::prepare(
                    "UPDATE leads SET status = 'converted' WHERE id = ? AND status IN ('new','contacted','qualified')"
                )->execute([$leadIdToConvert]);
            }
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
     * Atualiza reserva com verificação de conflito numa transação.
     * @param array<string, mixed> $d
     */
    public static function updateSafely(int $id, array $d): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            self::lockCarForUpdate($pdo, (int) $d['car_id']);
            self::assertNoConflict(
                $pdo,
                (int) $d['car_id'],
                (string) $d['pickup_date'],
                (string) $d['pickup_time'],
                (string) $d['return_date'],
                (string) $d['return_time'],
                $id
            );
            self::updateWithPdo($pdo, $id, $d);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /** @param array<string, mixed> $d */
    public static function create(array $d): int
    {
        return self::insertWithPdo(Database::pdo(), $d);
    }

    /** @param array<string, mixed> $d */
    public static function update(int $id, array $d): void
    {
        self::updateWithPdo(Database::pdo(), $id, $d);
    }

    /**
     * Verifica sobreposição de reservas activas para o mesmo veículo.
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
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function lockCarForUpdate(PDO $pdo, int $carId): void
    {
        unset($pdo);
        $stmt = Database::prepare('SELECT id FROM cars WHERE id = ? FOR UPDATE');
        $stmt->execute([$carId]);
        if (!$stmt->fetch()) {
            throw new RuntimeException('Car not found');
        }
    }

    private static function assertNoConflict(
        PDO $pdo,
        int $carId,
        string $pickupDate,
        string $pickupTime,
        string $returnDate,
        string $returnTime,
        ?int $excludeReservationId
    ): void {
        $pickup = $pickupDate . ' ' . $pickupTime;
        $ret = $returnDate . ' ' . $returnTime;
        $sql = "SELECT id FROM reservations
                WHERE car_id = ? AND status IN " . self::BLOCKING . "
                AND CONCAT(pickup_date, ' ', pickup_time) < ?
                AND CONCAT(return_date, ' ', return_time) > ?
                LIMIT 1 FOR UPDATE";
        $params = [$carId, $ret, $pickup];
        if ($excludeReservationId !== null) {
            $sql = str_replace(' LIMIT 1 FOR UPDATE', ' AND id <> ? LIMIT 1 FOR UPDATE', $sql);
            $params[] = $excludeReservationId;
        }
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetch()) {
            throw new ReservationConflictException();
        }
    }

    private static function nextCodeLocked(PDO $pdo): string
    {
        unset($pdo);
        $lock = Database::query("SELECT GET_LOCK('titanium_res_code', 10)")->fetchColumn();
        if ((int) $lock !== 1) {
            throw new RuntimeException('Could not acquire code lock');
        }
        try {
            $year = (int) date('Y');
            $prefix = 'TRC-' . $year . '-';
            $stmt = Database::prepare(
                "SELECT code FROM reservations WHERE code LIKE ? ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$prefix . '%']);
            $last = $stmt->fetchColumn();
            $n = 1;
            if (is_string($last) && preg_match('/TRC-\d+-(\d+)/', $last, $m)) {
                $n = (int) $m[1] + 1;
            }
            // TRC-YYYY-NNNN = 13 chars (coluna code ≥ VARCHAR(16); lock já serializa)
            return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
        } finally {
            Database::query("SELECT RELEASE_LOCK('titanium_res_code')");
        }
    }

    /**
     * Monta INSERT/UPDATE adaptando-se a colunas opcionais (hotel / extra_charges).
     * @param array<string, mixed> $d
     * @return array{cols: list<string>, vals: list<mixed>}
     */
    private static function writableReservationFields(array $d, bool $forInsert): array
    {
        $cols = [];
        $vals = [];
        $map = [
            'code' => $d['code'] ?? null,
            'customer_id' => $d['customer_id'],
            'car_id' => $d['car_id'],
            'operator_id' => $d['operator_id'] ?? null,
            'pickup_location_id' => $d['pickup_location_id'],
            'return_location_id' => $d['return_location_id'],
            'pickup_hotel_name' => $d['pickup_hotel_name'] ?? null,
            'return_hotel_name' => $d['return_hotel_name'] ?? null,
            'pickup_date' => $d['pickup_date'],
            'pickup_time' => $d['pickup_time'],
            'return_date' => $d['return_date'],
            'return_time' => $d['return_time'],
            'daily_rate' => $d['daily_rate'],
            'total_days' => $d['total_days'],
            'total_amount' => $d['total_amount'],
            'discount' => $d['discount'],
            'extra_charges' => $d['extra_charges'] ?? 0,
            'final_amount' => $d['final_amount'],
            'status' => $d['status'],
            'payment_status' => $d['payment_status'],
            'payment_method' => $d['payment_method'] ?? null,
            'notes' => $d['notes'] ?? null,
        ];
        $optional = ['pickup_hotel_name', 'return_hotel_name', 'extra_charges'];
        $hotelNotes = [];
        if (!Schema::hasColumn('reservations', 'pickup_hotel_name') && !empty($d['pickup_hotel_name'])) {
            $hotelNotes[] = 'Hotel retirada: ' . (string) $d['pickup_hotel_name'];
        }
        if (!Schema::hasColumn('reservations', 'return_hotel_name') && !empty($d['return_hotel_name'])) {
            $hotelNotes[] = 'Hotel devolução: ' . (string) $d['return_hotel_name'];
        }
        if ($hotelNotes !== []) {
            $extra = implode(' | ', $hotelNotes);
            $map['notes'] = trim((string) ($map['notes'] ?? '') . ($map['notes'] ? "\n" : '') . $extra) ?: null;
        }
        foreach ($map as $col => $val) {
            if (!$forInsert && ($col === 'code' || $col === 'operator_id')) {
                continue;
            }
            if (in_array($col, $optional, true) && !Schema::hasColumn('reservations', $col)) {
                continue;
            }
            $cols[] = $col;
            $vals[] = $val;
        }
        return ['cols' => $cols, 'vals' => $vals];
    }

    /** @param array<string, mixed> $d */
    private static function insertWithPdo(PDO $pdo, array $d): int
    {
        $built = self::writableReservationFields($d, true);
        $placeholders = implode(',', array_fill(0, count($built['cols']), '?'));
        $colList = implode(', ', $built['cols']);
        $stmt = Database::prepare("INSERT INTO reservations ({$colList}) VALUES ({$placeholders})");
        $stmt->execute($built['vals']);
        $newId = (int) $pdo->lastInsertId();
        self::reconcileCarStatus((int) $d['car_id']);
        return $newId;
    }

    /** @param array<string, mixed> $d */
    private static function updateWithPdo(PDO $pdo, int $id, array $d): void
    {
        unset($pdo);
        $built = self::writableReservationFields($d, false);
        $set = implode(', ', array_map(static fn (string $c): string => "{$c}=?", $built['cols']));
        $params = $built['vals'];
        $params[] = $id;
        $stmt = Database::prepare("UPDATE reservations SET {$set} WHERE id=?");
        $stmt->execute($params);
        self::reconcileCarStatus((int) $d['car_id']);
    }

    public static function setStatus(int $id, string $status): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = Database::prepare('SELECT car_id, status FROM reservations WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                $pdo->rollBack();
                return;
            }
            $carId = (int) $row['car_id'];
            $oldStatus = (string) $row['status'];
            Database::prepare('UPDATE reservations SET status = ? WHERE id = ?')->execute([$status, $id]);
            self::syncCarStatus($pdo, $carId, $oldStatus, $status);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param array{damage_notes?: ?string, extra_charges?: float, photo_path?: ?string, created_by?: ?int}|null $inspection
     */
    public static function checkIn(int $id, int $mileage, string $fuelLevel, ?array $inspection = null): void
    {
        $allowed = ['empty', 'quarter', 'half', 'three_quarter', 'full'];
        if (!in_array($fuelLevel, $allowed, true)) {
            throw new InvalidArgumentException('invalid fuel');
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = Database::prepare('SELECT car_id, status FROM reservations WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row || in_array($row['status'], ['cancelled', 'completed'], true)) {
                throw new RuntimeException('invalid reservation');
            }
            $carId = (int) $row['car_id'];
            $oldStatus = (string) $row['status'];
            Database::prepare(
                'UPDATE reservations SET status = ?, actual_pickup_at = NOW(), pickup_mileage = ?, fuel_level_pickup = ? WHERE id = ?'
            )->execute(['active', $mileage, $fuelLevel, $id]);
            Database::prepare('UPDATE cars SET status = ?, mileage = GREATEST(mileage, ?) WHERE id = ?')
                ->execute(['rented', $mileage, $carId]);
            self::syncCarStatus($pdo, $carId, $oldStatus, 'active');
            if ($inspection !== null) {
                self::insertInspectionWithPdo($pdo, $id, 'pickup', $mileage, $fuelLevel, $inspection);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param array{damage_notes?: ?string, extra_charges?: float, photo_path?: ?string, created_by?: ?int}|null $inspection
     */
    public static function checkOut(int $id, int $mileage, string $fuelLevel, ?array $inspection = null): void
    {
        $allowed = ['empty', 'quarter', 'half', 'three_quarter', 'full'];
        if (!in_array($fuelLevel, $allowed, true)) {
            throw new InvalidArgumentException('invalid fuel');
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = Database::prepare('SELECT car_id, status FROM reservations WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row || ($row['status'] ?? '') !== 'active') {
                throw new RuntimeException('invalid reservation');
            }
            $carId = (int) $row['car_id'];
            $oldStatus = (string) $row['status'];
            Database::prepare(
                'UPDATE reservations SET status = ?, actual_return_at = NOW(), return_mileage = ?, fuel_level_return = ? WHERE id = ?'
            )->execute(['completed', $mileage, $fuelLevel, $id]);
            Database::prepare('UPDATE cars SET mileage = GREATEST(mileage, ?) WHERE id = ?')->execute([$mileage, $carId]);
            self::syncCarStatus($pdo, $carId, $oldStatus, 'completed');
            if ($inspection !== null) {
                self::insertInspectionWithPdo($pdo, $id, 'return', $mileage, $fuelLevel, $inspection);
                self::addExtraChargesWithPdo($pdo, $id, (float) ($inspection['extra_charges'] ?? 0));
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function reconcileCarStatus(int $carId): void
    {
        $pdo = Database::pdo();
        $stmt = Database::prepare(
            "SELECT COUNT(*) FROM reservations WHERE car_id = ? AND status IN ('pending','confirmed','active')"
        );
        $stmt->execute([$carId]);
        $blocking = (int) $stmt->fetchColumn();
        if ($blocking > 0) {
            Database::prepare(
                "UPDATE cars SET status = 'rented' WHERE id = ? AND status NOT IN ('maintenance','inactive')"
            )->execute([$carId]);
        } else {
            Database::prepare(
                "UPDATE cars SET status = 'available' WHERE id = ? AND status = 'rented'"
            )->execute([$carId]);
        }
    }

    private static function syncCarStatus(PDO $pdo, int $carId, string $oldStatus, string $newStatus): void
    {
        unset($oldStatus);
        if (in_array($newStatus, ['active'], true)) {
            Database::prepare("UPDATE cars SET status = 'rented' WHERE id = ? AND status <> 'maintenance' AND status <> 'inactive'")
                ->execute([$carId]);
            return;
        }
        if (in_array($newStatus, ['completed', 'cancelled'], true)) {
            $stmt = Database::prepare(
                "SELECT COUNT(*) FROM reservations WHERE car_id = ? AND status IN ('pending','confirmed','active')"
            );
            $stmt->execute([$carId]);
            if ((int) $stmt->fetchColumn() === 0) {
                Database::prepare("UPDATE cars SET status = 'available' WHERE id = ? AND status = 'rented'")
                    ->execute([$carId]);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function forCustomer(int $customerId, int $limit = 50, ?int $operatorId = null): array
    {
        $sql = 'SELECT r.id, r.code, r.pickup_date, r.return_date, r.status, r.final_amount,
                       car.brand, car.model, car.license_plate
                FROM reservations r
                JOIN cars car ON car.id = r.car_id
                WHERE r.customer_id = ?';
        $params = [$customerId];
        if ($operatorId !== null) {
            $sql .= ' AND r.operator_id = ?';
            $params[] = $operatorId;
        }
        $sql .= ' ORDER BY r.pickup_date DESC LIMIT ' . (int) $limit;
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Calendar events in range
     * @return array<int, array<string, mixed>>
     */
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
        $stmt = Database::prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
