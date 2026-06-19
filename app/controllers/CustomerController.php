<?php

declare(strict_types=1);

final class CustomerController
{
    public function index(): void
    {
        PartnerForbiddenMiddleware::handle();
        $createdBy = Auth::isOwner() ? null : Auth::id();
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $filters = array_filter([
            'q' => trim((string) ($_GET['q'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
        ], static fn (string $v): bool => $v !== '');
        $p = Customer::paginated($page, $perPage, $createdBy, $filters);
        View::render('customers/index', [
            'title' => Lang::get('nav.customers'),
            'customers' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/customers'),
            'listQuery' => $filters,
            'filters' => $filters,
        ], 'main');
    }

    public function show(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        $c = Customer::find((int) $id);
        if (!$c || !AccessControl::canAccessCustomer($c)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('customers/show', [
            'title' => (string) $c['full_name'],
            'customer' => $c,
            'reservations' => Reservation::forCustomer((int) $id),
            'isAnonymized' => Customer::isAnonymized($c),
        ], 'main');
    }

    public function createForm(): void
    {
        PartnerForbiddenMiddleware::handle();
        View::render('customers/create', ['title' => Lang::get('customer.create'), 'customer' => null], 'main');
    }

    public function create(): void
    {
        PartnerForbiddenMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/customers/create'));
            exit;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidCustomerData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/customers/create'));
            exit;
        }
        $d['created_by'] = Auth::id();
        $attachment = CustomerAttachment::store($_FILES['attachment'] ?? null, null);
        if ($attachment === false) {
            header('Location: ' . Router::url('/customers/create'));
            exit;
        }
        $d['attachment_path'] = $attachment;
        try {
            $id = Customer::create($d);
            Audit::log(Auth::id(), 'create', 'customer', $id, null, $d);
            Flash::success(Lang::get('flash.saved'));
            header('Location: ' . Router::url('/customers'));
            exit;
        } catch (Throwable $e) {
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/customers/create'));
            exit;
        }
    }

    public function editForm(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        $c = Customer::find((int) $id);
        if (!$c || !AccessControl::canAccessCustomer($c)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        if (Customer::isAnonymized($c)) {
            Flash::error(Lang::get('customer.anonymized_locked'));
            header('Location: ' . Router::url('/customers/' . $id));
            exit;
        }
        View::render('customers/edit', ['title' => Lang::get('customer.edit'), 'customer' => $c], 'main');
    }

    public function update(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/customers/' . $id . '/edit'));
            exit;
        }
        $old = Customer::find((int) $id);
        if (!$old || !AccessControl::canAccessCustomer($old)) {
            http_response_code(404);
            return;
        }
        if (Customer::isAnonymized($old)) {
            Flash::error(Lang::get('customer.anonymized_locked'));
            header('Location: ' . Router::url('/customers/' . $id));
            exit;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidCustomerData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/customers/' . $id . '/edit'));
            exit;
        }
        $attachment = CustomerAttachment::store($_FILES['attachment'] ?? null, isset($old['attachment_path']) ? (string) $old['attachment_path'] : null);
        if ($attachment === false) {
            header('Location: ' . Router::url('/customers/' . $id . '/edit'));
            exit;
        }
        $d['attachment_path'] = $attachment;
        Customer::update((int) $id, $d);
        Audit::log(Auth::id(), 'update', 'customer', (int) $id, $old, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/customers'));
        exit;
    }

    public function downloadAttachment(string $id): void
    {
        PartnerForbiddenMiddleware::handle();
        $c = Customer::find((int) $id);
        if (!$c || empty($c['attachment_path']) || !AccessControl::canAccessCustomer($c)) {
            http_response_code(404);
            return;
        }
        $path = CustomerAttachment::resolvePath((string) $c['attachment_path']);
        if ($path === null) {
            http_response_code(404);
            return;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path) ?: 'application/octet-stream';
        $filename = basename($path);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }

    /** @param array<string, mixed> $post */
    private function sanitize(array $post): array
    {
        return [
            'type' => (string) ($post['type'] ?? 'individual'),
            'full_name' => trim((string) ($post['full_name'] ?? '')),
            'document' => preg_replace('/\D/', '', (string) ($post['document'] ?? '')),
            'email' => trim((string) ($post['email'] ?? '')),
            'phone' => trim((string) ($post['phone'] ?? '')),
            'address' => trim((string) ($post['address'] ?? '')) ?: null,
            'city' => trim((string) ($post['city'] ?? '')) ?: null,
            'state' => trim((string) ($post['state'] ?? '')) ?: null,
            'zip_code' => trim((string) ($post['zip_code'] ?? '')) ?: null,
            'notes' => trim((string) ($post['notes'] ?? '')) ?: null,
        ];
    }

    /** @param array<string, mixed> $d */
    private function isValidCustomerData(array $d): bool
    {
        if (($d['full_name'] ?? '') === '' || ($d['document'] ?? '') === '' || ($d['phone'] ?? '') === '') {
            return false;
        }
        $type = (string) ($d['type'] ?? 'individual');
        if (!in_array($type, ['individual', 'company'], true)) {
            return false;
        }
        $email = (string) ($d['email'] ?? '');
        return $email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function exportData(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/customers/' . $id));
            exit;
        }
        $payload = CustomerService::exportPayload((int) $id);
        if ($payload === null) {
            http_response_code(404);
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="customer-' . (int) $id . '-export.json"');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        Audit::log(Auth::id(), 'export', 'customer', (int) $id, null, ['lgpd' => true]);
    }

    public function anonymizeData(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/customers/' . $id));
            exit;
        }
        $result = CustomerService::anonymize((int) $id);
        if (!$result['ok']) {
            $key = match ($result['error']) {
                'active_reservations' => 'customer.anonymize_active',
                'already_anonymized' => 'customer.anonymize_done',
                default => 'error.404_title',
            };
            Flash::error(Lang::get($key));
            header('Location: ' . Router::url('/customers/' . $id));
            exit;
        }
        Audit::log(Auth::id(), 'anonymize', 'customer', (int) $id, null, ['lgpd' => true]);
        Flash::success(Lang::get('customer.anonymize_ok'));
        header('Location: ' . Router::url('/customers/' . $id));
        exit;
    }
}
