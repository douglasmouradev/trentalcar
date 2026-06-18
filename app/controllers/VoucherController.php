<?php

declare(strict_types=1);

final class VoucherController
{
    public function show(string $id): void
    {
        $r = Reservation::find((int) $id);
        if (!$r || !AccessControl::canAccessReservation($r)) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        View::render('reservations/voucher', [
            'title' => Lang::get('reservation.voucher') . ' ' . $r['code'],
            'r' => $r,
        ], 'bare');
    }
}
