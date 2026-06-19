<?php

declare(strict_types=1);

final class ApiController
{
    private const RESERVATION_STATUSES = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];

    public function customersSearch(): void
    {
        PartnerForbiddenMiddleware::handleJson();
        ApiRateLimiter::guardJson();
        header('Content-Type: application/json; charset=utf-8');
        $q = (string) ($_GET['q'] ?? '');
        $createdBy = Auth::isOwner() ? null : Auth::id();
        $rows = Customer::searchAutocomplete($q, 20, $createdBy);
        echo json_encode(['ok' => true, 'data' => $rows], JSON_THROW_ON_ERROR);
    }

    public function reservationConflict(): void
    {
        PartnerForbiddenMiddleware::handleJson();
        ApiRateLimiter::guardJson();
        header('Content-Type: application/json; charset=utf-8');
        $carId = (int) ($_GET['car_id'] ?? 0);
        $pickupDate = (string) ($_GET['pickup_date'] ?? '');
        $returnDate = (string) ($_GET['return_date'] ?? '');
        $pickupTime = (string) ($_GET['pickup_time'] ?? '09:00:00');
        $returnTime = (string) ($_GET['return_time'] ?? '18:00:00');
        if (strlen($pickupTime) === 5) {
            $pickupTime .= ':00';
        }
        if (strlen($returnTime) === 5) {
            $returnTime .= ':00';
        }
        $exclude = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : null;
        if ($exclude !== null && $exclude > 0 && !AccessControl::canAccessReservationId($exclude)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'conflict' => false, 'error' => 'forbidden'], JSON_THROW_ON_ERROR);
            return;
        }
        if ($carId <= 0 || $pickupDate === '' || $returnDate === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'conflict' => false, 'error' => 'invalid'], JSON_THROW_ON_ERROR);
            return;
        }
        if (!Auth::partnerMayViewCar($carId)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'conflict' => false, 'error' => 'forbidden'], JSON_THROW_ON_ERROR);
            return;
        }
        $conflict = Reservation::hasConflict($carId, $pickupDate, $pickupTime, $returnDate, $returnTime, $exclude);
        echo json_encode(['ok' => true, 'conflict' => $conflict], JSON_THROW_ON_ERROR);
    }

    public function calendarEvents(): void
    {
        PartnerForbiddenMiddleware::handleJson();
        ApiRateLimiter::guardJson();
        header('Content-Type: application/json; charset=utf-8');
        $start = (string) ($_GET['start'] ?? date('Y-m-01'));
        $end = (string) ($_GET['end'] ?? date('Y-m-t'));
        if (!self::isValidDate($start) || !self::isValidDate($end)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'invalid_dates'], JSON_THROW_ON_ERROR);
            return;
        }
        $carId = isset($_GET['car_id']) ? (int) $_GET['car_id'] : 0;
        $status = (string) ($_GET['status'] ?? '');
        if ($status !== '' && !in_array($status, self::RESERVATION_STATUSES, true)) {
            $status = '';
        }
        if (Auth::isOwner()) {
            $operatorId = isset($_GET['operator_id']) ? (int) $_GET['operator_id'] : 0;
            $operatorId = $operatorId > 0 ? $operatorId : null;
        } else {
            $operatorId = Auth::id();
        }
        $events = Reservation::eventsBetween(
            $start,
            $end,
            $carId > 0 ? $carId : null,
            $operatorId,
            $status !== '' ? $status : null
        );
        echo json_encode(['ok' => true, 'data' => $events], JSON_THROW_ON_ERROR);
    }

    public function customersQuickCreate(): void
    {
        PartnerForbiddenMiddleware::handleJson();
        ApiRateLimiter::guardJson();
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false]);
            return;
        }
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $json = $_POST;
        }
        if (!Csrf::validate($json['_csrf'] ?? null)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'csrf']);
            return;
        }
        $d = [
            'type' => in_array((string) ($json['type'] ?? 'individual'), ['individual', 'company'], true)
                ? (string) $json['type']
                : 'individual',
            'full_name' => trim((string) ($json['full_name'] ?? '')),
            'document' => preg_replace('/\D/', '', (string) ($json['document'] ?? '')),
            'email' => trim((string) ($json['email'] ?? '')),
            'phone' => trim((string) ($json['phone'] ?? '')),
            'address' => null,
            'city' => null,
            'state' => null,
            'zip_code' => null,
            'notes' => null,
            'created_by' => Auth::id(),
        ];
        if ($d['full_name'] === '' || $d['document'] === '' || $d['phone'] === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'validation']);
            return;
        }
        if ($d['email'] !== '' && filter_var($d['email'], FILTER_VALIDATE_EMAIL) === false) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'validation']);
            return;
        }
        try {
            $id = Customer::create($d);
            echo json_encode([
                'ok' => true,
                'customer' => [
                    'id' => $id,
                    'full_name' => $d['full_name'],
                    'document' => $d['document'],
                ],
            ], JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            AppLog::error('api.customer_quick_create', ['error' => $e->getMessage()]);
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'db']);
        }
    }

    private static function isValidDate(string $date): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }
}
