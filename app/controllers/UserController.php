<?php

declare(strict_types=1);

final class UserController
{
    public function index(): void
    {
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = User::paginated($page, $perPage);
        View::render('users/index', [
            'title' => Lang::get('nav.users'),
            'users' => $p['rows'],
            'pagination' => $p,
            'paginationBase' => Router::url('/users'),
            'listQuery' => [],
        ], 'main');
    }

    public function createForm(): void
    {
        $allCars = Car::search([]);
        View::render('users/create', [
            'title' => Lang::get('user.create'),
            'user' => null,
            'allCars' => $allCars,
            'assignedCarIds' => [],
        ], 'main');
    }

    public function create(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        $role = self::normalizeRole($_POST['role'] ?? '');
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => $role,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'lang_pref' => in_array($_POST['lang_pref'] ?? '', ['pt-BR', 'en-US'], true) ? $_POST['lang_pref'] : 'pt-BR',
        ];
        $passErr = PasswordPolicy::validate($data['password']);
        if ($passErr !== null) {
            Flash::error(Lang::get('user.password_' . $passErr));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        $existing = User::findByEmail($data['email']);
        if ($existing) {
            Flash::error(Lang::get('user.email_taken'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        try {
            $id = User::createWithPartnerCars($data, self::carIdsFromPost());
        } catch (Throwable $e) {
            AppError::log($e);
            Flash::error(Lang::get('flash.error'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        Audit::log(Auth::id(), 'create', 'user', $id, null, ['email' => $data['email'], 'role' => $data['role']]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/users'));
        exit;
    }

    public function editForm(string $id): void
    {
        $u = User::find((int) $id);
        if (!$u) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        $allCars = Car::search([]);
        $assignedCarIds = ($u['role'] ?? '') === 'partner' ? UserCar::carIdsForUser((int) $u['id']) : [];
        View::render('users/edit', [
            'title' => Lang::get('user.edit'),
            'user' => $u,
            'allCars' => $allCars,
            'assignedCarIds' => $assignedCarIds,
        ], 'main');
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            header('Location: ' . Router::url('/users/' . $id . '/edit'));
            exit;
        }
        $uid = (int) $id;
        $old = User::find($uid);
        if (!$old) {
            http_response_code(404);
            return;
        }
        $role = self::normalizeRole($_POST['role'] ?? '');
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'role' => $role,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'lang_pref' => in_array($_POST['lang_pref'] ?? '', ['pt-BR', 'en-US'], true) ? $_POST['lang_pref'] : 'pt-BR',
        ];
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass !== '') {
            $passErr = PasswordPolicy::validate($pass);
            if ($passErr !== null) {
                Flash::error(Lang::get('user.password_' . $passErr));
                header('Location: ' . Router::url('/users/' . $id . '/edit'));
                exit;
            }
            $data['password'] = $pass;
        }
        if (User::emailTakenByOther($data['email'], $uid)) {
            Flash::error(Lang::get('user.email_taken'));
            header('Location: ' . Router::url('/users/' . $id . '/edit'));
            exit;
        }
        User::update($uid, $data);
        if ($role === 'partner') {
            UserCar::syncForUser($uid, self::carIdsFromPost());
        } else {
            UserCar::deleteForUser($uid);
        }
        if ((int) $old['id'] === (int) Auth::id()) {
            Auth::refreshUserFromDb();
        }
        Audit::log(Auth::id(), 'update', 'user', $uid, ['email' => $old['email'], 'role' => $old['role']], ['email' => $data['email'], 'role' => $role]);
        Flash::success(Lang::get('flash.saved'));
        header('Location: ' . Router::url('/users'));
        exit;
    }

    private static function normalizeRole(mixed $role): string
    {
        $r = is_string($role) ? $role : '';
        return in_array($r, ['owner', 'operator', 'partner'], true) ? $r : 'operator';
    }

    /** @return array<int, int> */
    private static function carIdsFromPost(): array
    {
        $raw = $_POST['car_ids'] ?? [];
        if (!is_array($raw)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_map(static fn ($v) => (int) $v, $raw), static fn ($id) => $id > 0)));
    }
}
