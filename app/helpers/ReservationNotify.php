<?php

declare(strict_types=1);

final class ReservationNotify
{
    /** @param array<string, mixed> $reservation */
    public static function sendConfirmation(int $reservationId, array $reservation): void
    {
        if (($reservation['status'] ?? '') !== 'confirmed') {
            return;
        }
        $customer = Customer::find((int) ($reservation['customer_id'] ?? 0));
        if ($customer === null || empty($customer['email'])) {
            return;
        }
        $full = Reservation::find($reservationId);
        if ($full === null) {
            return;
        }
        $subject = Lang::get('mail.reservation_confirmed_subject', ['code' => $full['code']]);
        $body = Lang::get('mail.reservation_confirmed_body', [
            'code' => $full['code'],
            'pickup' => $full['pickup_date'] . ' ' . substr((string) $full['pickup_time'], 0, 5),
            'return' => $full['return_date'] . ' ' . substr((string) $full['return_time'], 0, 5),
            'car' => $full['brand'] . ' ' . $full['model'] . ' (' . $full['license_plate'] . ')',
            'total' => Formatter::money((float) $full['final_amount']),
            'voucher_url' => Router::url('/reservations/' . $reservationId . '/voucher'),
        ]);
        Mail::send((string) $customer['email'], $subject, $body);
    }
}
