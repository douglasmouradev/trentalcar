<?php

declare(strict_types=1);

final class ReservationController
{
    public function index(): void
    {
        PartnerForbiddenMiddleware::handle();
        $op = Auth::isOwner() ? null : Auth::id();
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $filters = array_filter([
            'status' => trim((string) ($_GET['status'] ?? '')),
            'payment_status' => trim((string) ($_GET['payment_status'] ?? '')),
            'q' => trim((string) ($_GET['q'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ], static fn (string $v): bool => $v !== '');
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
        PartnerForbiddenMiddleware::handle();
        $cars = Auth::isOwner()
            ? Car::search([])
            : Car::search(['status' => 'available']);
        $operators = Database::pdo()->query("SELECT id, name FROM users WHERE role = 'operator' AND is_active = 1 ORDER BY name")->fetchAll();
        View::render('reservations/calendar', [
            'title' => Lang::get('nav.calendar'),
            'cars' => $cars,
            'operators' => $operators,
        ], 'main');
    }

    public function createForm(): void
    {
        PartnerForbiddenMiddleware::handle();
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
        PartnerForbiddenMiddleware::handle();
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
        try {
            $id = Reservation::createSafely($d);
        } catch (ReservationConflictException) {
            Flash::error(Lang::get('reservation.conflict'));
            $_SESSION['reservation_draft'] = $_POST;
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        $leadId = (int) ($_POST['lead_id'] ?? 0);
        if ($leadId > 0 && Lead::find($leadId) !== null) {
            Lead::updateStatus($leadId, 'converted', null);
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
        PartnerForbiddenMiddleware::handle();
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
        PartnerForbiddenMiddleware::handle();
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
        PartnerForbiddenMiddleware::handle();
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
        PartnerForbiddenMiddleware::handle();
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
        PartnerForbiddenMiddleware::handle();
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
        try {
            Reservation::checkIn((int) $id, $mileage, $fuel);
            if (Schema::hasTable('reservation_inspections')) {
                $photo = InspectionUpload::store($_FILES['photo_pickup'] ?? null, (int) $id, 'pickup');
                ReservationInspection::create([
                    'reservation_id' => (int) $id,
                    'kind' => 'pickup',
                    'mileage' => $mileage,
                    'fuel_level' => $fuel,
                    'damage_notes' => $damageNotes,
                    'extra_charges' => 0,
                    'photo_path' => $photo,
                    'created_by' => Auth::id(),
                ]);
            }
        } catch (Throwable $e) {
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
        PartnerForbiddenMiddleware::handle();
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
        try {
            Reservation::checkOut((int) $id, $mileage, $fuel);
            if (Schema::hasTable('reservation_inspections')) {
                $photo = InspectionUpload::store($_FILES['photo_return'] ?? null, (int) $id, 'return');
                ReservationInspection::create([
                    'reservation_id' => (int) $id,
                    'kind' => 'return',
                    'mileage' => $mileage,
                    'fuel_level' => $fuel,
                    'damage_notes' => $damageNotes,
                    'extra_charges' => $extraCharges,
                    'photo_path' => $photo,
                    'created_by' => Auth::id(),
                ]);
                if ($extraCharges > 0) {
                    Reservation::addExtraCharges((int) $id, $extraCharges);
                }
            }
        } catch (Throwable $e) {
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
        $d['final_amount'] = max(0, round((float) $d['total_amount'] - (float) $d['discount'], 2));

        if (!Auth::isOwner()) {
            $d['daily_rate'] = (float) ($car['daily_rate'] ?? 0);
            $totalDays = $d['total_days'];
            $d['total_amount'] = round($d['daily_rate'] * $totalDays, 2);
            $d['final_amount'] = max(0, round($d['total_amount'] - $d['discount'], 2));
        }

        return $d;
    }

    /** @param array<string, mixed> $post */
    private function normalize(array $post, ?int $excludeId = null, ?array $old = null): array
    {
        $pickupDate = (string) ($post['pickup_date'] ?? '');
        $returnDate = (string) ($post['return_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickupDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $returnDate)) {
            throw new InvalidArgumentException('invalid date');
        }
        $pickupTime = (string) ($post['pickup_time'] ?? '09:00');
        $returnTime = (string) ($post['return_time'] ?? '18:00');
        if (strlen($pickupTime) === 5) {
            $pickupTime .= ':00';
        }
        if (strlen($returnTime) === 5) {
            $returnTime .= ':00';
        }
        $d1 = new DateTimeImmutable($pickupDate);
        $d2 = new DateTimeImmutable($returnDate);
        $totalDays = max(1, (int) $d1->diff($d2)->format('%a') + 1);
        $daily = (float) ($post['daily_rate'] ?? 0);
        $discount = Auth::isOwner() ? max(0.0, (float) ($post['discount'] ?? 0)) : 0.0;
        $totalAmount = round($daily * $totalDays, 2);
        $final = max(0, round($totalAmount - $discount, 2));

        $status = (string) ($post['status'] ?? 'pending');
        $paymentStatus = (string) ($post['payment_status'] ?? 'unpaid');
        $paymentMethod = ($post['payment_method'] ?? '') !== '' ? (string) $post['payment_method'] : null;

        if (Auth::isOwner()) {
            $allowedStatuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
            if (!in_array($status, $allowedStatuses, true)) {
                $status = 'pending';
            }
            $allowedPayments = ['unpaid', 'partial', 'paid'];
            if (!in_array($paymentStatus, $allowedPayments, true)) {
                $paymentStatus = 'unpaid';
            }
            $allowedMethods = ['cash', 'credit_card', 'debit_card', 'pix', 'transfer'];
            if ($paymentMethod !== null && !in_array($paymentMethod, $allowedMethods, true)) {
                $paymentMethod = null;
            }
        } else {
            $allowedOperatorStatus = ['pending', 'confirmed', 'active'];
            if (!in_array($status, $allowedOperatorStatus, true)) {
                $status = is_array($old) ? (string) ($old['status'] ?? 'pending') : 'pending';
            }
            $paymentStatus = is_array($old) ? (string) ($old['payment_status'] ?? 'unpaid') : 'unpaid';
            $paymentMethod = is_array($old) && !empty($old['payment_method']) ? (string) $old['payment_method'] : null;
        }

        return [
            'customer_id' => (int) ($post['customer_id'] ?? 0),
            'car_id' => (int) ($post['car_id'] ?? 0),
            'pickup_location_id' => (int) ($post['pickup_location_id'] ?? 0),
            'return_location_id' => (int) ($post['return_location_id'] ?? 0),
            'pickup_date' => $pickupDate,
            'pickup_time' => $pickupTime,
            'return_date' => $returnDate,
            'return_time' => $returnTime,
            'daily_rate' => $daily,
            'total_days' => $totalDays,
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'final_amount' => $final,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'notes' => trim((string) ($post['notes'] ?? '')) ?: null,
        ];
    }

    /** @param array<string, mixed> $draft
     *  @return array<string, mixed>
     */
    private static function draftToReservation(array $draft): array
    {
        $keys = [
            'customer_id', 'car_id', 'pickup_location_id', 'return_location_id',
            'pickup_date', 'pickup_time', 'return_date', 'return_time',
            'daily_rate', 'discount', 'status', 'payment_status', 'payment_method', 'notes',
        ];
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $draft) && $draft[$key] !== '') {
                $out[$key] = $draft[$key];
            }
        }
        return $out;
    }
}
