<?php

declare(strict_types=1);

final class ReservationValidator
{
    private const STATUSES = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
    private const PAYMENTS = ['unpaid', 'partial', 'paid'];
    private const METHODS = ['cash', 'credit_card', 'debit_card', 'pix', 'transfer'];

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed>|null $old
     * @return array{ok: bool, error: string|null, data: array<string, mixed>|null}
     */
    public static function validate(array $post, bool $isOwner, ?array $old = null): array
    {
        $pickupDate = trim((string) ($post['pickup_date'] ?? ''));
        $returnDate = trim((string) ($post['return_date'] ?? ''));
        $pickupTime = trim((string) ($post['pickup_time'] ?? '09:00'));
        $returnTime = trim((string) ($post['return_time'] ?? '18:00'));

        if (!self::validDate($pickupDate) || !self::validDate($returnDate)) {
            return self::fail('reservation.invalid_dates');
        }
        if ($returnDate < $pickupDate) {
            return self::fail('reservation.invalid_range');
        }

        if (strlen($pickupTime) === 5) {
            $pickupTime .= ':00';
        }
        if (strlen($returnTime) === 5) {
            $returnTime .= ':00';
        }

        $customerId = (int) ($post['customer_id'] ?? 0);
        $carId = (int) ($post['car_id'] ?? 0);
        $pickupLoc = (int) ($post['pickup_location_id'] ?? 0);
        $returnLoc = (int) ($post['return_location_id'] ?? 0);

        if ($customerId <= 0 || Customer::find($customerId) === null) {
            return self::fail('reservation.customer_required');
        }
        $car = Car::find($carId);
        if ($car === null) {
            return self::fail('reservation.invalid_car');
        }
        if (!Location::isActive($pickupLoc) || !Location::isActive($returnLoc)) {
            return self::fail('reservation.invalid_location');
        }

        $pickupHotel = trim((string) ($post['pickup_hotel_name'] ?? ''));
        $returnHotel = trim((string) ($post['return_hotel_name'] ?? ''));
        if (Location::isHotelLocation($pickupLoc)) {
            if ($pickupHotel === '') {
                return self::fail('reservation.hotel_required');
            }
            $pickupHotel = mb_substr($pickupHotel, 0, 120);
        } else {
            $pickupHotel = null;
        }
        if (Location::isHotelLocation($returnLoc)) {
            if ($returnHotel === '') {
                return self::fail('reservation.hotel_required');
            }
            $returnHotel = mb_substr($returnHotel, 0, 120);
        } else {
            $returnHotel = null;
        }

        $status = (string) ($post['status'] ?? 'pending');
        if (!in_array($status, self::STATUSES, true) || $status === 'cancelled') {
            $status = 'pending';
        }
        if (!$isOwner) {
            if (!in_array($status, ['pending', 'confirmed', 'active'], true)) {
                $status = is_array($old) ? (string) ($old['status'] ?? 'pending') : 'pending';
            }
        }

        $paymentStatus = (string) ($post['payment_status'] ?? 'unpaid');
        if (!in_array($paymentStatus, self::PAYMENTS, true)) {
            $paymentStatus = 'unpaid';
        }

        $paymentMethod = ($post['payment_method'] ?? '') !== '' ? (string) $post['payment_method'] : null;
        if ($paymentMethod !== null && !in_array($paymentMethod, self::METHODS, true)) {
            $paymentMethod = null;
        }

        if (!$isOwner) {
            $paymentStatus = is_array($old) ? (string) ($old['payment_status'] ?? 'unpaid') : 'unpaid';
            if (!in_array($paymentStatus, self::PAYMENTS, true)) {
                $paymentStatus = 'unpaid';
            }
            $paymentMethod = is_array($old) && !empty($old['payment_method']) ? (string) $old['payment_method'] : null;
        }

        $totalDays = PricingHelper::rentalDays($pickupDate, $pickupTime, $returnDate, $returnTime);
        $daily = max(0.0, (float) ($post['daily_rate'] ?? 0));
        $discount = $isOwner ? max(0.0, (float) ($post['discount'] ?? 0)) : 0.0;
        $totalAmount = round($daily * $totalDays, 2);
        $final = max(0.0, round($totalAmount - $discount, 2));

        return [
            'ok' => true,
            'error' => null,
            'data' => [
                'customer_id' => $customerId,
                'car_id' => $carId,
                'pickup_location_id' => $pickupLoc,
                'return_location_id' => $returnLoc,
                'pickup_hotel_name' => $pickupHotel,
                'return_hotel_name' => $returnHotel,
                'pickup_date' => $pickupDate,
                'pickup_time' => $pickupTime,
                'return_date' => $returnDate,
                'return_time' => $returnTime,
                'daily_rate' => $daily,
                'total_days' => $totalDays,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $final,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'notes' => trim((string) ($post['notes'] ?? '')) ?: null,
            ],
        ];
    }

    private static function validDate(string $d): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1;
    }

    /** @return array{ok: false, error: string, data: null} */
    private static function fail(string $key): array
    {
        return ['ok' => false, 'error' => $key, 'data' => null];
    }
}
