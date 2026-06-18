<?php

declare(strict_types=1);

final class LocationController
{
    public function index(): void
    {
        View::render('locations/index', [
            'title' => Lang::get('nav.locations'),
            'locations' => Location::all(),
        ], 'main');
    }

    public function createForm(): void
    {
        View::render('locations/form', ['title' => Lang::get('location.create'), 'location' => null], 'main');
    }

    public function create(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/locations/create'));
            exit;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidLocationData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/locations/create'));
            exit;
        }
        $id = Location::create($d);
        Audit::log(Auth::id(), 'create', 'location', $id, null, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/locations'));
        exit;
    }

    public function editForm(string $id): void
    {
        $loc = Location::find((int) $id);
        if (!$loc) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('locations/form', ['title' => Lang::get('location.edit'), 'location' => $loc], 'main');
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/locations/' . $id . '/edit'));
            exit;
        }
        $old = Location::find((int) $id);
        if (!$old) {
            http_response_code(404);
            return;
        }
        $d = $this->sanitize($_POST);
        if (!$this->isValidLocationData($d)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/locations/' . $id . '/edit'));
            exit;
        }
        Location::update((int) $id, $d);
        Audit::log(Auth::id(), 'update', 'location', (int) $id, $old, $d);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/locations'));
        exit;
    }

    /** @param array<string, mixed> $post */
    private function sanitize(array $post): array
    {
        return [
            'name' => trim((string) ($post['name'] ?? '')),
            'address' => trim((string) ($post['address'] ?? '')),
            'city' => trim((string) ($post['city'] ?? '')),
            'state' => trim((string) ($post['state'] ?? '')),
            'zip_code' => trim((string) ($post['zip_code'] ?? '')) ?: null,
            'phone' => trim((string) ($post['phone'] ?? '')) ?: null,
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];
    }

    /** @param array<string, mixed> $d */
    private function isValidLocationData(array $d): bool
    {
        return ($d['name'] ?? '') !== ''
            && ($d['address'] ?? '') !== ''
            && ($d['city'] ?? '') !== ''
            && ($d['state'] ?? '') !== '';
    }
}
