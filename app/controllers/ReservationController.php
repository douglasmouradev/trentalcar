<?php

declare(strict_types=1);

final class ReservationController
{
    private const STATUSES = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
    private const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid', 'refunded'];

    public function index(): void
    {
        $op = Auth::isOwner() ? null : Auth::id();
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $filters = $this->sanitizeListFilters($_GET);
        $p = Reservation::forOperatorPaginated($op, $page, $perPage, $filters);
        View::render('reservations/index', [
            'title' => Lang::get('nav.reservations'),
            'reservations' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/reservations'),
            'listQuery' => $filters,
            'filters' => $filters,
        ], 'main');
    }

    public function calendar(): void
    {
        $cars = Auth::isOwner()
            ? Car::search([])
            : Car::search(['status' => 'available']);
        $operators = Database::query("SELECT id, name FROM users WHERE role = 'operator' AND is_active = 1 ORDER BY name")->fetchAll();
        View::render('reservations/calendar', [
            'title' => Lang::get('nav.calendar'),
            'cars' => $cars,
            'operators' => $operators,
        ], 'main');
    }

    public function createForm(): void
    {
        $cars = Auth::isOwner()
            ? Car::search([])
            : Car::search(['status' => 'available']);
        $leadPrefill = $_SESSION['lead_convert'] ?? null;
        unset($_SESSION['lead_convert']);
        $draft = $_SESSION['reservation_draft'] ?? null;
        unset($_SESSION['reservation_draft']);

        $reservation = null;
        if (is_array($leadPrefill)) {
            $reservation = [
                'pickup_date' => $leadPrefill['pickup_date'] ?? '',
                'return_date' => $leadPrefill['return_date'] ?? '',
                'car_id' => $leadPrefill['car_id'] ?? null,
                'pickup_location_id' => $leadPrefill['pickup_location_id'] ?? null,
                'return_location_id' => $leadPrefill['return_location_id'] ?? null,
                'pickup_hotel_name' => $leadPrefill['pickup_hotel_name'] ?? '',
                'return_hotel_name' => $leadPrefill['return_hotel_name'] ?? '',
                'notes' => $leadPrefill['notes'] ?? '',
            ];
        }
        if (is_array($draft)) {
            $reservation = array_merge($reservation ?? [], self::draftToReservation($draft));
            if ($leadPrefill === null && !empty($draft['lead_id'])) {
                $leadPrefill = [
                    'lead_id' => (int) $draft['lead_id'],
                    'customer_name' => (string) ($draft['lead_customer_name'] ?? ''),
                    'customer_email' => (string) ($draft['lead_customer_email'] ?? ''),
                    'customer_phone' => (string) ($draft['lead_customer_phone'] ?? ''),
                ];
            }
        }
        Location::ensurePickupDefaults();
        View::render('reservations/create', [
            'title' => Lang::get('reservation.create'),
            'cars' => $cars,
            'locations' => Location::allActive(),
            'customers' => [],
            'reservation' => $reservation,
            'leadPrefill' => $leadPrefill,
        ], 'main');
    }

    public function create(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        $d = $this->buildReservationData($_POST, null, null);
        if ($d === null) {
            $_SESSION['reservation_draft'] = $_POST;
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        $d['operator_id'] = Auth::id();
        $leadId = (int) ($_POST['lead_id'] ?? 0);
        $leadId = ($leadId > 0 && Lead::find($leadId) !== null) ? $leadId : null;
        try {
            $id = Reservation::createSafely($d, $leadId);
        } catch (ReservationConflictException) {
            Flash::error(Lang::get('reservation.conflict'));
            $_SESSION['reservation_draft'] = $_POST;
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        } catch (Throwable $e) {
            AppLog::error('reservation.create_failed', ['error' => $e->getMessage()]);
            Flash::error(Lang::get('flash.error'));
            $_SESSION['reservation_draft'] = $_POST;
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        $code = (string) (Reservation::find($id)['code'] ?? '');
        Audit::log(Auth::id(), 'create', 'reservation', $id, null, $d);
        ReservationNotify::sendConfirmation($id, $d);
        Flash::successKey('flash.reservation_saved', ['code' => $code]);
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    public function show(string $id): void
    {
        $r = Reservation::find((int) $id);
        if (!$r || !AccessControl::canAccessReservation($r)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('reservations/show', [
            'title' => $r['code'],
            'r' => $r,
            'inspections' => Schema::hasTable('reservation_inspections')
                ? ReservationInspection::forReservation((int) $id)
                : [],
        ], 'main');
    }

    public function editForm(string $id): void
    {
        $r = Reservation::find((int) $id);
        if (!$r || !AccessControl::canAccessReservation($r)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        $cars = Auth::isOwner()
            ? Car::search([])
            : Car::search(['status' => 'available']);
        $customerScope = Auth::isOwner() ? null : Auth::id();
        $customers = Customer::all($customerScope);
        if (!Auth::isOwner()) {
            $cid = (int) ($r['customer_id'] ?? 0);
            if ($cid > 0 && !array_filter($customers, static fn (array $c): bool => (int) $c['id'] === $cid)) {
                $extra = Customer::find($cid);
                if ($extra && AccessControl::canAccessCustomer($extra)) {
                    $customers[] = $extra;
                }
            }
        }
        Location::ensurePickupDefaults();
        View::render('reservations/edit', [
            'title' => Lang::get('reservation.edit'),
            'r' => $r,
            'cars' => $cars,
            'locations' => Location::allActive(),
            'customers' => $customers,
        ], 'main');
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        }
        $old = Reservation::find((int) $id);
        if (!$old || !AccessControl::canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        $d = $this->buildReservationData($_POST, (int) $id, $old);
        if ($d === null) {
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        }
        try {
            Reservation::updateSafely((int) $id, $d);
        } catch (ReservationConflictException) {
            Flash::error(Lang::get('reservation.conflict'));
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        } catch (Throwable $e) {
            AppLog::error('reservation.update_failed', ['id' => $id, 'error' => $e->getMessage()]);
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        }
        Audit::log(Auth::id(), 'update', 'reservation', (int) $id, $old, $d);
        if (($d['status'] ?? '') === 'confirmed' && ($old['status'] ?? '') !== 'confirmed') {
            ReservationNotify::sendConfirmation((int) $id, $d);
        }
        Flash::successKey('flash.reservation_updated', ['code' => (string) ($old['code'] ?? '')]);
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    public function cancel(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        $old = Reservation::find((int) $id);
        if (!$old || !AccessControl::canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        if (in_array($old['status'] ?? '', ['cancelled', 'completed'], true)) {
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        Reservation::setStatus((int) $id, 'cancelled');
        Audit::log(Auth::id(), 'cancel', 'reservation', (int) $id, $old, ['status' => 'cancelled']);
        Flash::successKey('flash.reservation_cancelled', ['code' => (string) ($old['code'] ?? '')]);
        header('Location: ' . Router::url('/reservations'));
        exit;
    }

    public function checkIn(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        $old = Reservation::find((int) $id);
        if (!$old || !AccessControl::canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        $mileage = max(0, (int) ($_POST['pickup_mileage'] ?? 0));
        $fuel = (string) ($_POST['fuel_level_pickup'] ?? 'full');
        $damageNotes = trim((string) ($_POST['damage_notes_pickup'] ?? '')) ?: null;
        $photo = null;
        $inspection = null;
        if (Schema::hasTable('reservation_inspections')) {
            $inspection = [
                'damage_notes' => $damageNotes,
                'extra_charges' => 0,
                'photo_path' => null,
                'created_by' => Auth::id(),
            ];
        }
        try {
            if ($inspection !== null) {
                $photo = InspectionUpload::store($_FILES['photo_pickup'] ?? null, (int) $id, 'pickup');
                $inspection['photo_path'] = $photo;
            }
            Reservation::checkIn((int) $id, $mileage, $fuel, $inspection);
        } catch (Throwable $e) {
            InspectionUpload::deleteStored($photo);
            AppLog::error('reservation.checkin_failed', ['id' => $id, 'error' => $e->getMessage()]);
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        Audit::log(Auth::id(), 'checkin', 'reservation', (int) $id, $old, ['pickup_mileage' => $mileage, 'fuel_level_pickup' => $fuel]);
        Flash::success(Lang::get('reservation.checkin_ok'));
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    public function checkOut(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        $old = Reservation::find((int) $id);
        if (!$old || !AccessControl::canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        $mileage = max(0, (int) ($_POST['return_mileage'] ?? 0));
        $fuel = (string) ($_POST['fuel_level_return'] ?? 'full');
        $damageNotes = trim((string) ($_POST['damage_notes_return'] ?? '')) ?: null;
        $extraCharges = max(0.0, (float) ($_POST['extra_charges'] ?? 0));
        if (!empty($old['pickup_mileage']) && $mileage > 0 && $mileage < (int) $old['pickup_mileage']) {
            Flash::error(Lang::get('reservation.mileage_min_return'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        $photo = null;
        $inspection = null;
        if (Schema::hasTable('reservation_inspections')) {
            $inspection = [
                'damage_notes' => $damageNotes,
                'extra_charges' => $extraCharges,
                'photo_path' => null,
                'created_by' => Auth::id(),
            ];
        }
        try {
            if ($inspection !== null) {
                $photo = InspectionUpload::store($_FILES['photo_return'] ?? null, (int) $id, 'return');
                $inspection['photo_path'] = $photo;
            }
            Reservation::checkOut((int) $id, $mileage, $fuel, $inspection);
        } catch (Throwable $e) {
            InspectionUpload::deleteStored($photo);
            AppLog::error('reservation.checkout_failed', ['id' => $id, 'error' => $e->getMessage()]);
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        Audit::log(Auth::id(), 'checkout', 'reservation', (int) $id, $old, ['return_mileage' => $mileage, 'fuel_level_return' => $fuel]);
        Flash::success(Lang::get('reservation.checkout_ok'));
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed>|null $old
     * @return array<string, mixed>|null
     */
    private function buildReservationData(array $post, ?int $reservationId, ?array $old): ?array
    {
        try {
            $d = $this->normalize($post, $reservationId, $old);
        } catch (InvalidArgumentException $e) {
            Flash::error(Lang::get($e->getMessage()));
            return null;
        } catch (Throwable) {
            Flash::error(Lang::get('reservation.invalid_dates'));
            return null;
        }

        if (!AccessControl::canAccessCustomerId($d['customer_id'])) {
            Flash::error(Lang::get('reservation.invalid_customer'));
            return null;
        }

        $car = Car::find($d['car_id']);
        if ($car === null) {
            Flash::error(Lang::get('reservation.invalid_car'));
            return null;
        }

        if (!Auth::partnerMayViewCar((int) $car['id'])) {
            Flash::error(Lang::get('error.403_title'));
            return null;
        }

        $sameCarAsBefore = is_array($old) && (int) ($old['car_id'] ?? 0) === (int) $car['id'];
        if (!Auth::isOwner() && ($car['status'] ?? '') !== 'available' && !$sameCarAsBefore) {
            Flash::error(Lang::get('reservation.car_unavailable'));
            return null;
        }

        if (!Location::isActive($d['pickup_location_id']) || !Location::isActive($d['return_location_id'])) {
            Flash::error(Lang::get('reservation.invalid_location'));
            return null;
        }

        if ($d['return_date'] < $d['pickup_date']) {
            Flash::error(Lang::get('reservation.invalid_dates'));
            return null;
        }

        $promo = PricingHelper::applyWeeklyPromo(
            (int) $d['total_days'],
            (float) $d['daily_rate'],
            (string) ($car['category'] ?? 'standard'),
            (float) $d['discount']
        );
        $d['discount'] = $promo['discount_applied'];
        $extras = max(0.0, (float) ($d['extra_charges'] ?? 0));
        $d['extra_charges'] = $extras;
        $d['final_amount'] = max(0, round((float) $d['total_amount'] - (float) $d['discount'] + $extras, 2));

        if (!Auth::isOwner()) {
            $d['daily_rate'] = (float) ($car['daily_rate'] ?? 0);
            $totalDays = $d['total_days'];
            $d['total_amount'] = round($d['daily_rate'] * $totalDays, 2);
            $d['final_amount'] = max(0, round($d['total_amount'] - $d['discount'] + $extras, 2));
        }

        return $d;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed>|null $old
     * @return array<string, mixed>
     */
    private function normalize(array $post, ?int $excludeId = null, ?array $old = null): array
    {
        $result = ReservationService::validateInput($post, Auth::isOwner(), $old);
        if (!$result['ok'] || $result['data'] === null) {
            throw new InvalidArgumentException($result['error'] ?? 'reservation.invalid_dates');
        }
        return $result['data'];
    }

    /** @param array<string, mixed> $draft
     *  @return array<string, mixed>
     */
    private static function draftToReservation(array $draft): array
    {
        $keys = [
            'customer_id', 'car_id', 'pickup_location_id', 'return_location_id',
            'pickup_hotel_name', 'return_hotel_name',
            'pickup_date', 'pickup_time', 'return_date', 'return_time',
            'daily_rate', 'discount', 'extra_charges', 'status', 'payment_status', 'payment_method', 'notes',
        ];
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $draft) && $draft[$key] !== '') {
                $out[$key] = $draft[$key];
            }
        }
        return $out;
    }

    public function inspectionPhoto(string $id): void
    {
        $resId = (int) $id;
        if (!AccessControl::canAccessReservationId($resId)) {
            http_response_code(403);
            exit;
        }
        $file = basename((string) ($_GET['f'] ?? ''));
        if ($file === '') {
            http_response_code(404);
            exit;
        }
        $path = InspectionUpload::absolutePath($resId, $file);
        if ($path === null) {
            http_response_code(404);
            exit;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, string>
     */
    private function sanitizeListFilters(array $query): array
    {
        $status = trim((string) ($query['status'] ?? ''));
        $paymentStatus = trim((string) ($query['payment_status'] ?? ''));
        $filters = array_filter([
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
            'payment_status' => in_array($paymentStatus, self::PAYMENT_STATUSES, true) ? $paymentStatus : '',
            'q' => trim((string) ($query['q'] ?? '')),
            'date_from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($query['date_from'] ?? '')) === 1
                ? (string) $query['date_from']
                : '',
            'date_to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($query['date_to'] ?? '')) === 1
                ? (string) $query['date_to']
                : '',
        ], static fn (string $v): bool => $v !== '');

        return $filters;
    }
}
