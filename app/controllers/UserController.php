<?php

declare(strict_types=1);

final class UserController
{
    public function index(): void
    {
        $page = Pagination::currentPage();
        $perPage = Pagination::perPage();
        $p = User::paginated($page, $perPage, true);
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
        View::render('users/create', [
            'title' => Lang::get('user.create'),
            'user' => null,
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
        if (strlen($data['password']) < 8) {
            Flash::error(Lang::get('user.password_short'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        $policyError = PasswordPolicy::validate($data['password']);
        if ($policyError !== null) {
            Flash::error($policyError);
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        $data['must_change_password'] = 1;
        $existing = User::findByEmail($data['email']);
        if ($existing) {
            Flash::error(Lang::get('user.email_taken'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        if ($role === 'partner') {
            Flash::error(Lang::get('user.partner_use_module'));
            header('Location: ' . Router::url('/users/create'));
            exit;
        }
        try {
            $id = User::create($data);
        } catch (Throwable $e) {
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
        if (!$u || ($u['role'] ?? '') === 'partner') {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('users/edit', [
            'title' => Lang::get('user.edit'),
            'user' => $u,
            'allCars' => [],
            'assignedCarIds' => [],
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
        if (!$old || ($old['role'] ?? '') === 'partner') {
            http_response_code(404);
            return;
        }
        $role = self::normalizeRole($_POST['role'] ?? '');
        if ($role === 'partner') {
            Flash::error(Lang::get('user.partner_use_module'));
            header('Location: ' . Router::url('/users/' . $uid . '/edit'));
            exit;
        }
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'role' => $role,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'lang_pref' => in_array($_POST['lang_pref'] ?? '', ['pt-BR', 'en-US'], true) ? $_POST['lang_pref'] : 'pt-BR',
        ];
        if ($uid === (int) Auth::id() && ($old['role'] ?? '') === 'owner') {
            $data['is_active'] = 1;
            $data['role'] = 'owner';
        }
        if (($old['role'] ?? '') === 'owner' && $role !== 'owner' && User::countActiveOwners() <= 1) {
            Flash::error(Lang::get('user.last_owner'));
            header('Location: ' . Router::url('/users/' . $id . '/edit'));
            exit;
        }
        if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Flash::error(Lang::get('flash.validation_error'));
            header('Location: ' . Router::url('/users/' . $id . '/edit'));
            exit;
        }
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass !== '') {
            $policyError = PasswordPolicy::validate($pass);
            if ($policyError !== null) {
                Flash::error($policyError);
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
        return in_array($r, ['owner', 'operator'], true) ? $r : 'operator';
    }
}
