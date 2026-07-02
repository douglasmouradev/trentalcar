<?php

declare(strict_types=1);

final class PartnerController
{
    public function index(): void
    {
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = Partner::paginated($page, $perPage);
        View::render('partners/index', [
            'title' => Lang::get('nav.partners'),
            'partners' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/partners'),
            'listQuery' => [],
        ], 'main');
    }

    public function createForm(): void
    {
        View::render('partners/form', [
            'title' => Lang::get('partner.create'),
            'partner' => null,
            'allCars' => Car::search([]),
            'assignments' => [],
        ], 'main');
    }

    public function create(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/partners/create'));
            exit;
        }
        $data = $this->baseDataFromPost();
        if ($data === null) {
            header('Location: ' . Router::url('/partners/create'));
            exit;
        }
        $data['role'] = 'partner';
        $data['must_change_password'] = 1;
        if (User::findByEmail($data['email'])) {
            Flash::error(Lang::get('user.email_taken'));
            header('Location: ' . Router::url('/partners/create'));
            exit;
        }
        try {
            $id = User::create($data);
        } catch (Throwable) {
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/partners/create'));
            exit;
        }
        UserCar::syncWithQuotas($id, self::assignmentsFromPost());
        Audit::log(Auth::id(), 'create', 'partner', $id, null, ['email' => $data['email']]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/partners'));
        exit;
    }

    public function editForm(string $id): void
    {
        $partner = Partner::find((int) $id);
        if (!$partner) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('partners/form', [
            'title' => Lang::get('partner.edit'),
            'partner' => $partner,
            'allCars' => Car::search([]),
            'assignments' => UserCar::assignmentsForUser((int) $partner['id']),
        ], 'main');
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/partners/' . $id . '/edit'));
            exit;
        }
        $uid = (int) $id;
        $old = Partner::find($uid);
        if (!$old) {
            http_response_code(404);
            return;
        }
        $data = $this->baseDataFromPost(false);
        if ($data === null) {
            header('Location: ' . Router::url('/partners/' . $uid . '/edit'));
            exit;
        }
        if (User::emailTakenByOther($data['email'], $uid)) {
            Flash::error(Lang::get('user.email_taken'));
            header('Location: ' . Router::url('/partners/' . $uid . '/edit'));
            exit;
        }
        User::update($uid, $data);
        UserCar::syncWithQuotas($uid, self::assignmentsFromPost());
        Audit::log(Auth::id(), 'update', 'partner', $uid, ['email' => $old['email']], ['email' => $data['email']]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/partners'));
        exit;
    }

    /** @return array{name: string, email: string, role: string, phone: string, is_active: int, lang_pref: string, password?: string}|null */
    private function baseDataFromPost(bool $requirePassword = true): ?array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error(Lang::get('flash.validation_error'));
            return null;
        }
        if ($requirePassword) {
            if (strlen($pass) < 8) {
                Flash::error(Lang::get('user.password_short'));
                return null;
            }
            $policyError = PasswordPolicy::validate($pass);
            if ($policyError !== null) {
                Flash::error($policyError);
                return null;
            }
        } elseif ($pass !== '') {
            $policyError = PasswordPolicy::validate($pass);
            if ($policyError !== null) {
                Flash::error($policyError);
                return null;
            }
        }
        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'partner',
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'lang_pref' => in_array($_POST['lang_pref'] ?? '', ['pt-BR', 'en-US'], true) ? $_POST['lang_pref'] : 'pt-BR',
        ];
        if ($requirePassword) {
            $data['password'] = $pass;
        } elseif ($pass !== '') {
            $data['password'] = $pass;
        }
        return $data;
    }

    /** @return array<int, array{car_id: int, quota: float}> */
    private static function assignmentsFromPost(): array
    {
        $carIds = $_POST['car_ids'] ?? [];
        $quotas = $_POST['quota_percent'] ?? [];
        if (!is_array($carIds)) {
            return [];
        }
        $items = [];
        foreach ($carIds as $rawId) {
            $carId = (int) $rawId;
            if ($carId <= 0) {
                continue;
            }
            $quota = 100.0;
            if (is_array($quotas) && isset($quotas[$carId])) {
                $quota = (float) str_replace(',', '.', (string) $quotas[$carId]);
            } elseif (is_array($quotas) && isset($quotas[(string) $carId])) {
                $quota = (float) str_replace(',', '.', (string) $quotas[(string) $carId]);
            }
            $items[] = ['car_id' => $carId, 'quota' => max(0.01, min(100.0, $quota))];
        }
        return $items;
    }
}
