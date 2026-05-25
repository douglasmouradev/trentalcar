<?php

declare(strict_types=1);

final class ReservationController
{
    public function index(): void
    {
        PartnerForbiddenMiddleware::handle();
        $op = Auth::isOwner() ? null : Auth::id();
        $filters = Reservation::filtersFromQuery();
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = Reservation::forOperatorPaginated($op, $page, $perPage, $filters);
        View::render('reservations/index', [
            'title' => Lang::get('nav.reservations'),
            'reservations' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/reservations'),
            'listQuery' => array_filter($filters, static fn ($v) => $v !== ''),
            'filters' => $filters,
        ], 'main');
    }

    public function exportCsv(): void
    {
        PartnerForbiddenMiddleware::handle();
        $op = Auth::isOwner() ? null : Auth::id();
        $filters = Reservation::filtersFromQuery();
        $csvRows = [];
        foreach (Reservation::exportRows($op, $filters) as $row) {
            $csvRows[] = [
                (string) $row['code'],
                (string) $row['customer_name'],
                trim((string) $row['brand'] . ' ' . (string) $row['model'] . ' (' . (string) $row['license_plate'] . ')'),
                (string) $row['pickup_date'] . ' ' . substr((string) $row['pickup_time'], 0, 5),
                (string) $row['return_date'] . ' ' . substr((string) $row['return_time'], 0, 5),
                Lang::get('status.' . $row['status']),
                number_format((float) $row['final_amount'], 2, ',', ''),
                (string) $row['payment_status'],
            ];
        }
        CsvResponse::download(
            'reservas-' . date('Y-m-d') . '.csv',
            [
                Lang::get('reservation.code'),
                Lang::get('reservation.customer'),
                Lang::get('reservation.car'),
                Lang::get('reservation.pickup'),
                Lang::get('reservation.return'),
                Lang::get('reservation.status'),
                Lang::get('reservation.amount'),
                Lang::get('reservation.payment'),
            ],
            $csvRows
        );
    }

    public function calendar(): void
    {
        PartnerForbiddenMiddleware::handle();
        $cars = Car::search([]);
        $operators = Database::pdo()->query("SELECT id, name FROM users WHERE role = 'operator' ORDER BY name")->fetchAll();
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
        $prefill = $_SESSION['reservation_prefill'] ?? null;
        unset($_SESSION['reservation_prefill']);
        View::render('reservations/create', [
            'title' => Lang::get('reservation.create'),
            'cars' => $cars,
            'locations' => Location::allActive(),
            'selectedCustomer' => null,
            'reservation' => is_array($prefill) ? $prefill : null,
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
        $validated = ReservationValidator::validate($_POST, Auth::isOwner());
        if (!$validated['ok']) {
            Flash::error(Lang::get($validated['error'] ?? 'flash.error'));
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        $d = $validated['data'];
        $d['operator_id'] = Auth::id();
        $id = Reservation::createAtomic($d);
        if ($id === false) {
            Flash::error(Lang::get('reservation.conflict'));
            header('Location: ' . Router::url('/reservations/create'));
            exit;
        }
        Audit::log(Auth::id(), 'create', 'reservation', $id, null, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    public function show(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        $r = Reservation::find((int) $id);
        if (!$r || (!$this->canAccessReservation($r))) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('reservations/show', ['title' => $r['code'], 'r' => $r], 'main');
    }

    public function editForm(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        $r = Reservation::find((int) $id);
        if (!$r || !$this->canAccessReservation($r)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        $cars = Auth::isOwner()
            ? Car::search([])
            : Car::search(['status' => 'available']);
        View::render('reservations/edit', [
            'title' => Lang::get('reservation.edit'),
            'r' => $r,
            'cars' => $cars,
            'locations' => Location::allActive(),
            'selectedCustomer' => Customer::find((int) $r['customer_id']),
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
        if (!$old || !$this->canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        $validated = ReservationValidator::validate($_POST, Auth::isOwner());
        if (!$validated['ok']) {
            Flash::error(Lang::get($validated['error'] ?? 'flash.error'));
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        }
        $d = $validated['data'];
        if (!Reservation::updateAtomic((int) $id, $d)) {
            Flash::error(Lang::get('reservation.conflict'));
            header('Location: ' . Router::url('/reservations/' . $id . '/edit'));
            exit;
        }
        Audit::log(Auth::id(), 'update', 'reservation', (int) $id, $old, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    public function cancel(string $id): void
    {
        $this->transition($id, 'cancel');
    }

    public function confirm(string $id): void
    {
        $this->transition($id, 'confirm');
    }

    public function activate(string $id): void
    {
        $this->transition($id, 'activate');
    }

    public function complete(string $id): void
    {
        $this->transition($id, 'complete');
    }

    private function transition(string $id, string $action): void
    {
        PartnerForbiddenMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        $old = Reservation::find((int) $id);
        if (!$old || !$this->canAccessReservation($old)) {
            http_response_code(404);
            return;
        }
        $result = Reservation::transition((int) $id, $action);
        if ($result !== true) {
            Flash::error(Lang::get((string) $result));
            header('Location: ' . Router::url('/reservations/' . $id));
            exit;
        }
        Audit::log(Auth::id(), $action, 'reservation', (int) $id, $old, ['action' => $action]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/reservations/' . $id));
        exit;
    }

    private function canAccessReservation(array $r): bool
    {
        if (Auth::isOwner()) {
            return true;
        }
        return (int) $r['operator_id'] === (int) Auth::id();
    }
}
