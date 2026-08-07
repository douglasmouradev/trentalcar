<?php

declare(strict_types=1);

final class LeadsController
{
    public function index(): void
    {
        if (!Auth::isStaff()) {
            http_response_code(403);
            View::render('errors/403', ['title' => Lang::get('error.403_title')], 'main');
            return;
        }
        $status = trim((string) ($_GET['status'] ?? ''));
        $q = trim((string) ($_GET['q'] ?? ''));
        $page = Pagination::currentPage();
        $p = Lead::paginated($page, Pagination::perPage(), $status !== '' ? $status : null, $q !== '' ? $q : null);
        View::render('leads/index', [
            'title' => Lang::get('leads.title'),
            'leads' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/leads'),
            'listQuery' => array_filter(['status' => $status, 'q' => $q]),
            'filters' => ['status' => $status, 'q' => $q],
        ], 'main');
    }

    public function show(string $id): void
    {
        if (!Auth::isStaff()) {
            http_response_code(403);
            View::render('errors/403', ['title' => Lang::get('error.403_title')], 'main');
            return;
        }
        $lead = Lead::find((int) $id);
        if (!$lead) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        $waMsg = LeadNotify::whatsappMessage(
            (string) $lead['full_name'],
            (string) $lead['inicio'],
            (string) $lead['fim'],
            (string) $lead['local'],
            !empty($lead['car_brand']) ? trim((string) $lead['car_brand'] . ' ' . (string) ($lead['car_model'] ?? '')) : null
        );
        View::render('leads/show', [
            'title' => Lang::get('leads.detail'),
            'lead' => $lead,
            'whatsappUrl' => Contact::whatsappUrl($waMsg),
        ], 'main');
    }

    public function update(string $id): void
    {
        if (!Auth::isStaff()) {
            http_response_code(403);
            Flash::error(Lang::get('error.403_title'));
            Redirect::to('/leads');
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/leads/' . $id));
            exit;
        }
        $lead = Lead::find((int) $id);
        if (!$lead) {
            http_response_code(404);
            Flash::error(Lang::get('error.404_title'));
            Redirect::to('/leads');
        }
        $status = (string) ($_POST['status'] ?? 'new');
        $notes = trim((string) ($_POST['notes'] ?? '')) ?: null;
        $allowed = Auth::isOwner()
            ? Lead::STATUSES
            : ['contacted', 'discarded'];
        if (!in_array($status, $allowed, true)) {
            $status = (string) ($lead['status'] ?? 'new');
        }
        Lead::updateStatus((int) $id, $status, $notes);
        Audit::log(Auth::id(), 'update', 'lead', (int) $id, $lead, ['status' => $status, 'notes' => $notes]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/leads/' . $id));
        exit;
    }

    public function convert(string $id): void
    {
        if (!Auth::isOwner()) {
            http_response_code(403);
            Flash::error(Lang::get('error.403_title'));
            Redirect::to('/leads/' . $id);
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/leads/' . $id));
            exit;
        }
        $lead = Lead::find((int) $id);
        if (!$lead) {
            http_response_code(404);
            Flash::error(Lang::get('error.404_title'));
            Redirect::to('/leads');
        }
        Location::ensurePickupDefaults();
        $pickupLocId = Location::resolveIdFromLeadLocal((string) ($lead['local'] ?? ''));
        $returnLocId = Location::resolveIdFromLeadLocal((string) ($lead['local_devolucao'] ?? $lead['local'] ?? ''));
        $_SESSION['lead_convert'] = [
            'lead_id' => (int) $lead['id'],
            'customer_name' => (string) $lead['full_name'],
            'customer_email' => (string) $lead['email'],
            'customer_phone' => (string) $lead['phone'],
            'pickup_date' => (string) $lead['inicio'],
            'return_date' => (string) $lead['fim'],
            'car_id' => !empty($lead['car_id']) ? (int) $lead['car_id'] : null,
            'pickup_location_id' => $pickupLocId,
            'return_location_id' => $returnLocId,
            'pickup_hotel_name' => LeadPickupOptions::hotelNameFromStored((string) ($lead['local'] ?? '')),
            'return_hotel_name' => LeadPickupOptions::hotelNameFromStored((string) ($lead['local_devolucao'] ?? $lead['local'] ?? '')),
            'notes' => Lang::get('leads.convert_note', [
                'local' => (string) $lead['local'],
                'return' => (string) ($lead['local_devolucao'] ?? $lead['local']),
            ]),
        ];
        header('Location: ' . Router::url('/reservations/create'));
        exit;
    }
}
