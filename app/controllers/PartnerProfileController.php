<?php

declare(strict_types=1);

final class PartnerProfileController
{
    public function show(): void
    {
        if (!Auth::isPartner()) {
            http_response_code(403);
            View::render('errors/403', ['title' => Lang::get('error.403_title')], 'main');
            return;
        }
        $uid = Auth::id();
        if ($uid === null) {
            header('Location: ' . Router::url('/login'));
            exit;
        }
        $user = User::find($uid);
        if (!$user) {
            http_response_code(404);
            View::render('errors/404', ['title' => Lang::get('error.404_title')], 'main');
            return;
        }
        $assignments = UserCar::assignmentsForUser($uid);
        $revenueByCar = UserCar::revenueShareByCar($uid);
        $revenueMonth = UserCar::revenueShareMonth($uid);
        $revenueHistory = UserCar::revenueByMonth($uid, 6);
        $reservations = UserCar::reservationsForPartner($uid, 30);
        View::render('partner/profile', [
            'title' => Lang::get('partner.my_profile'),
            'user' => $user,
            'assignments' => $assignments,
            'revenueByCar' => $revenueByCar,
            'revenueMonth' => $revenueMonth,
            'revenueHistory' => $revenueHistory,
            'reservations' => $reservations,
        ], 'main');
    }

    public function exportCsv(): void
    {
        if (!Auth::isPartner()) {
            http_response_code(403);
            return;
        }
        $uid = Auth::id();
        if ($uid === null) {
            return;
        }
        $rows = UserCar::reservationsForPartner($uid, 500);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="partner-reservations-' . date('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        if ($out === false) {
            throw new RuntimeException('Failed to open CSV output stream');
        }
        fputcsv($out, [
            Lang::get('reservation.code'),
            Lang::get('reservation.customer'),
            Lang::get('car.model'),
            Lang::get('car.plate'),
            Lang::get('reservation.pickup'),
            Lang::get('reservation.return'),
            Lang::get('reservation.status'),
            Lang::get('partner.revenue_share'),
        ], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['code'],
                $r['customer_name'],
                ($r['brand'] ?? '') . ' ' . ($r['model'] ?? ''),
                $r['license_plate'],
                $r['pickup_date'],
                $r['return_date'],
                Lang::get('status.' . ($r['status'] ?? 'pending')),
                number_format((float) ($r['share_amount'] ?? 0), 2, ',', '.'),
            ], ';');
        }
        fclose($out);
        exit;
    }
}
