<?php

declare(strict_types=1);

final class ConsultController
{
    public function form(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }
        $reservation = $_SESSION['consult_result'] ?? null;
        unset($_SESSION['consult_result']);
        View::render('consult/index', [
            'title' => Lang::get('consult.title'),
            'reservation' => is_array($reservation) ? $reservation : null,
            'error' => $_SESSION['consult_error'] ?? null,
        ], 'bare');
        unset($_SESSION['consult_error']);
    }

    public function lookup(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['consult_error'] = Lang::get('error.csrf');
            header('Location: ' . Router::url('/consultar'));
            exit;
        }
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $email = trim((string) ($_POST['email'] ?? ''));
        $r = Reservation::findByCodeAndEmail($code, $email);
        if ($r === null) {
            $_SESSION['consult_error'] = Lang::get('consult.not_found');
            header('Location: ' . Router::url('/consultar'));
            exit;
        }
        $_SESSION['consult_result'] = $r;
        header('Location: ' . Router::url('/consultar'));
        exit;
    }
}
