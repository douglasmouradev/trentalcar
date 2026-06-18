<?php

declare(strict_types=1);

final class AccessControl
{
    /** @param array<string, mixed> $customer */
    public static function canAccessCustomer(array $customer): bool
    {
        if (Auth::isOwner()) {
            return true;
        }
        return (int) ($customer['created_by'] ?? 0) === (int) Auth::id();
    }

    public static function canAccessCustomerId(int $customerId): bool
    {
        if ($customerId <= 0) {
            return false;
        }
        $customer = Customer::find($customerId);
        return $customer !== null && self::canAccessCustomer($customer);
    }

    /** @param array<string, mixed> $reservation */
    public static function canAccessReservation(array $reservation): bool
    {
        if (Auth::isOwner()) {
            return true;
        }
        return (int) ($reservation['operator_id'] ?? 0) === (int) Auth::id();
    }

    public static function canAccessReservationId(int $reservationId): bool
    {
        if ($reservationId <= 0) {
            return false;
        }
        $r = Reservation::find($reservationId);
        return $r !== null && self::canAccessReservation($r);
    }
}
