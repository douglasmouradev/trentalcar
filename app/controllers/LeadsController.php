<?php

declare(strict_types=1);

final class LeadsController
{
    public function index(): void
    {
        $status = isset($_GET['status']) ? (string) $_GET['status'] : '';
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = Lead::paginated($page, $perPage, $status !== '' ? $status : null);
        $listQuery = $status !== '' ? ['status' => $status] : [];
        View::render('leads/index', [
            'title' => Lang::get('nav.leads'),
            'leads' => $p['rows'],
            'statusFilter' => $status,
            'pagination' => $p,
            'paginationBase' => Router::url('/leads'),
            'listQuery' => $listQuery,
        ], 'main');
    }

    public function exportCsv(): void
    {
        $status = isset($_GET['status']) ? (string) $_GET['status'] : '';
        $csvRows = [];
        foreach (Lead::exportRows($status !== '' ? $status : null) as $row) {
            $csvRows[] = [
                (string) $row['id'],
                (string) $row['location_text'],
                trim(implode(' | ', array_filter([
                    (string) ($row['contact_name'] ?? ''),
                    (string) ($row['contact_phone'] ?? ''),
                    (string) ($row['contact_email'] ?? ''),
                ]))),
                (string) $row['start_date'] . ' → ' . (string) $row['end_date'],
                (string) $row['status'],
                (string) $row['created_at'],
            ];
        }
        CsvResponse::download(
            'leads-' . date('Y-m-d') . '.csv',
            [
                'ID',
                Lang::get('lead.location'),
                Lang::get('lead.contact'),
                Lang::get('lead.dates'),
                Lang::get('lead.status'),
                Lang::get('lead.received'),
            ],
            $csvRows
        );
    }

    public function updateStatus(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/leads'));
            exit;
        }
        $lead = Lead::find((int) $id);
        if (!$lead) {
            http_response_code(404);
            return;
        }
        $status = (string) ($_POST['status'] ?? 'new');
        $allowed = ['new', 'contacted', 'converted', 'archived'];
        if (!in_array($status, $allowed, true)) {
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/leads'));
            exit;
        }
        $notes = trim((string) ($_POST['notes'] ?? '')) ?: null;
        Lead::setStatus((int) $id, $status, $notes);
        Audit::log(Auth::id(), 'update', 'lead', (int) $id, $lead, ['status' => $status, 'notes' => $notes]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/leads'));
        exit;
    }

    public function convert(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/leads'));
            exit;
        }
        $lead = Lead::find((int) $id);
        if (!$lead) {
            http_response_code(404);
            return;
        }
        $note = 'Lead #' . (int) $lead['id'] . ' — ' . (string) $lead['location_text'];
        if (!empty($lead['contact_name'])) {
            $note .= ' | ' . (string) $lead['contact_name'];
        }
        if (!empty($lead['contact_phone'])) {
            $note .= ' | ' . (string) $lead['contact_phone'];
        }
        if (!empty($lead['contact_email'])) {
            $note .= ' | ' . (string) $lead['contact_email'];
        }
        $_SESSION['reservation_prefill'] = [
            'pickup_date' => (string) $lead['start_date'],
            'return_date' => (string) $lead['end_date'],
            'notes' => $note,
        ];
        Lead::setStatus((int) $id, 'converted', Lang::get('lead.converted'));
        Audit::log(Auth::id(), 'convert', 'lead', (int) $id, $lead, null);
        Flash::success(Lang::get('lead.convert_redirect'));
        header('Location: ' . Router::url('/reservations/create'));
        exit;
    }
}
