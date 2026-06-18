<?php

declare(strict_types=1);

final class CustomerController
{
    private const ATTACHMENT_PATTERN = '/^cust_[a-f0-9]{16}\.(pdf|jpg|png|webp|doc|docx)$/';

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
        $attachment = $this->handleAttachmentUpload($_FILES['attachment'] ?? null, null);
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
        $d = $this->sanitize($_POST);
        if (!$this->isValidCustomerData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/customers/' . $id . '/edit'));
            exit;
        }
        $attachment = $this->handleAttachmentUpload($_FILES['attachment'] ?? null, $old);
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
        $path = self::resolveAttachmentPath((string) $c['attachment_path']);
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

    /**
     * @param array<string,mixed>|null $file
     * @param array<string,mixed>|null $existing
     * @return string|null false em falha de validação/upload
     */
    private function handleAttachmentUpload(?array $file, ?array $existing): string|null|false
    {
        if (empty($file) || empty($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
            return $existing['attachment_path'] ?? null;
        }

        $app = Config::app();
        $max = (int) ($app['max_upload'] ?? 5242880);
        if (($file['size'] ?? 0) > $max) {
            Flash::error(Lang::get('upload.too_large'));
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file((string) $file['tmp_name']);
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        if (!isset($allowed[$mime])) {
            Flash::error(Lang::get('upload.invalid_type'));
            return false;
        }

        $dir = BASE_PATH . '/storage/customers';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'cust_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
            Flash::error(Lang::get('upload.failed'));
            return false;
        }

        if (!empty($existing['attachment_path'])) {
            $oldFs = self::resolveAttachmentPath((string) $existing['attachment_path']);
            if ($oldFs !== null && is_file($oldFs)) {
                @unlink($oldFs);
            }
        }

        return $name;
    }

    private static function resolveAttachmentPath(string $stored): ?string
    {
        $name = basename(parse_url($stored, PHP_URL_PATH) ?: $stored);
        if (!preg_match(self::ATTACHMENT_PATTERN, $name)) {
            return null;
        }
        $base = realpath(BASE_PATH . '/storage/customers');
        if ($base === false) {
            return null;
        }
        $full = realpath($base . DIRECTORY_SEPARATOR . $name);
        if ($full === false || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $full;
    }
}
