<?php

declare(strict_types=1);

final class CarController
{
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'category' => $_GET['category'] ?? '',
            'brand' => $_GET['brand'] ?? '',
            'q' => $_GET['q'] ?? '',
        ];
        if (Auth::isPartner()) {
            $filters['restrict_to_car_ids'] = Auth::partnerCarIds();
        }
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = Car::searchPaginated($filters, $page, $perPage);
        $listQuery = array_filter(
            ['status' => $filters['status'], 'category' => $filters['category'], 'brand' => $filters['brand'], 'q' => $filters['q']],
            static fn (mixed $v): bool => is_string($v) && $v !== ''
        );
        View::render('cars/index', [
            'title' => Lang::get('nav.cars'),
            'cars' => $p['rows'],
            'filters' => $filters,
            'canEdit' => Auth::isOwner(),
            'pagination' => $p,
            'paginationBase' => Router::url('/cars'),
            'listQuery' => $listQuery,
        ], 'main');
    }

    public function show(string $id): void
    {
        $car = Car::find((int) $id);
        if (!$car) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        if (!Auth::partnerMayViewCar((int) $car['id'])) {
            http_response_code(403);
            View::render('errors/403', ['title' => Lang::get('error.403_title')], 'main');
            return;
        }
        $myQuota = null;
        $carPartners = [];
        if (Auth::isPartner()) {
            $myQuota = UserCar::quotaForUserCar((int) Auth::id(), (int) $car['id']);
        } elseif (Auth::isOwner()) {
            $carPartners = UserCar::partnersForCar((int) $car['id']);
        }
        View::render('cars/show', [
            'title' => $car['brand'] . ' ' . $car['model'],
            'car' => $car,
            'myQuota' => $myQuota,
            'carPartners' => $carPartners,
        ], 'main');
    }

    public function createForm(): void
    {
        $locations = Location::allActive();
        View::render('cars/create', ['title' => Lang::get('car.create'), 'locations' => $locations, 'car' => null], 'main');
    }

    public function create(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/cars/create'));
            exit;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidCarData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/cars/create'));
            exit;
        }
        $upload = $this->handleUpload('image');
        if ($upload === false) {
            header('Location: ' . Router::url('/cars/create'));
            exit;
        }
        $d['image_url'] = $upload ?? ($d['image_url'] ?? null);
        $id = Car::create($d);
        Audit::log(Auth::id(), 'create', 'car', $id, null, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/cars/' . $id));
        exit;
    }

    public function editForm(string $id): void
    {
        $car = Car::find((int) $id);
        if (!$car) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('cars/edit', [
            'title' => Lang::get('car.edit'),
            'car' => $car,
            'locations' => Location::allActive(),
        ], 'main');
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/cars/' . $id . '/edit'));
            exit;
        }
        $old = Car::find((int) $id);
        if (!$old) {
            http_response_code(404);
            return;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidCarData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/cars/' . $id . '/edit'));
            exit;
        }
        $uploaded = $this->handleUpload('image');
        if ($uploaded === false) {
            header('Location: ' . Router::url('/cars/' . $id . '/edit'));
            exit;
        }
        if (is_string($uploaded)) {
            $d['image_url'] = $uploaded;
        } else {
            $d['image_url'] = $old['image_url'];
        }
        Car::update((int) $id, $d);
        Audit::log(Auth::id(), 'update', 'car', (int) $id, $old, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/cars/' . $id));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/cars'));
            exit;
        }
        $old = Car::find((int) $id);
        if ($old) {
            Audit::log(Auth::id(), 'delete', 'car', (int) $id, $old, null);
            try {
                Car::delete((int) $id);
                Flash::success(Lang::get('flash.deleted'));
            } catch (RuntimeException) {
                Flash::error(Lang::get('car.delete_blocked_reservations'));
            }
        }
        header('Location: ' . Router::url('/cars'));
        exit;
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function sanitize(array $post): array
    {
        $colorHex = trim((string) ($post['color_hex'] ?? '#CCCCCC'));
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorHex)) {
            $colorHex = '#CCCCCC';
        }

        $category = (string) ($post['category'] ?? 'standard');
        $allowedCategories = ['economy', 'standard', 'suv', 'luxury', 'van', 'truck'];
        if (!in_array($category, $allowedCategories, true)) {
            $category = 'standard';
        }

        $status = (string) ($post['status'] ?? 'available');
        $allowedStatuses = ['available', 'rented', 'maintenance', 'inactive'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'available';
        }

        $transmission = (string) ($post['transmission'] ?? 'automatic');
        if (!in_array($transmission, ['manual', 'automatic'], true)) {
            $transmission = 'automatic';
        }

        $fuel = (string) ($post['fuel'] ?? 'flex');
        $allowedFuels = ['flex', 'gasoline', 'diesel', 'electric', 'hybrid'];
        if (!in_array($fuel, $allowedFuels, true)) {
            $fuel = 'flex';
        }

        return [
            'license_plate' => strtoupper(trim((string) ($post['license_plate'] ?? ''))),
            'brand' => trim((string) ($post['brand'] ?? '')),
            'model' => trim((string) ($post['model'] ?? '')),
            'year' => (int) ($post['year'] ?? date('Y')),
            'color' => trim((string) ($post['color'] ?? '')),
            'color_hex' => $colorHex,
            'category' => $category,
            'seats' => (int) ($post['seats'] ?? 5),
            'transmission' => $transmission,
            'fuel' => $fuel,
            'daily_rate' => (float) ($post['daily_rate'] ?? 0),
            'status' => $status,
            'location_id' => (int) ($post['location_id'] ?? 0),
            'mileage' => (int) ($post['mileage'] ?? 0),
            'monthly_fuel' => max(0.0, (float) ($post['monthly_fuel'] ?? 0)),
            'monthly_toll' => max(0.0, (float) ($post['monthly_toll'] ?? 0)),
            'monthly_wash' => max(0.0, (float) ($post['monthly_wash'] ?? 0)),
            'monthly_maintenance' => max(0.0, (float) ($post['monthly_maintenance'] ?? 0)),
            'monthly_extra' => max(0.0, (float) ($post['monthly_extra'] ?? 0)),
            'notes' => trim((string) ($post['notes'] ?? '')) ?: null,
        ];
    }

    /** @param array<string, mixed> $d */
    private function isValidCarData(array $d): bool
    {
        return ($d['license_plate'] ?? '') !== ''
            && ($d['brand'] ?? '') !== ''
            && ($d['model'] ?? '') !== ''
            && (int) ($d['location_id'] ?? 0) > 0
            && Location::isActive((int) $d['location_id']);
    }

    /** @return string|null file URL, null if no upload, false on validation failure */
    private function handleUpload(string $field): string|null|false
    {
        if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return null;
        }
        $app = Config::app();
        $max = (int) ($app['max_upload'] ?? 5242880);
        if (($_FILES[$field]['size'] ?? 0) > $max) {
            Flash::error(Lang::get('upload.too_large'));
            return false;
        }
        $tmp = (string) $_FILES[$field]['tmp_name'];
        $mime = ImageUpload::detectMime($tmp);
        if ($mime === null) {
            Flash::error(Lang::get('upload.invalid_type'));
            return false;
        }
        $rel = $app['upload_path'] ?? 'public/assets/uploads';
        $dir = BASE_PATH . '/' . $rel;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'car_' . bin2hex(random_bytes(8)) . '.' . ImageUpload::MIME_EXT[$mime];
        $dest = $dir . '/' . $name;
        if (!ImageUpload::saveFromTmp($tmp, $dest, $mime)) {
            Flash::error(Lang::get('upload.failed'));
            return false;
        }
        $webPath = '/assets/uploads/' . $name;
        return Router::url($webPath);
    }
}
