<?php

declare(strict_types=1);

final class DashboardController
{
    public function index(): void
    {
        if (Auth::isPartner()) {
            header('Location: ' . Router::url('/partner/profile'));
            exit;
        }

        $isOwner = Auth::isOwner();
        $uid = Auth::id() ?? 0;
        $pdo = Database::pdo();

        if (!$isOwner && $uid > 0) {
            $metrics = DashboardStats::ownerMetrics($uid, false);
            View::render('dashboard/index', [
                'title' => Lang::get('nav.dashboard'),
                'isOwner' => false,
                'isPartner' => false,
                'partnerCars' => [],
                'partnerActiveRes' => 0,
                'revenueMonth' => 0.0,
                'revenuePrevMonth' => 0.0,
                'revenueDelta' => null,
                'fleet' => 0,
                'activeRes' => 0,
                'occupancy' => 0,
                'unpaid' => 0,
                'chartDays' => [],
                'revenueByCategory' => [],
                'returns' => [],
                'maintenance' => [],
                'myToday' => $metrics['myToday'],
                'myTodayCount' => $metrics['myTodayCount'],
                'alerts' => DashboardAlerts::collect($pdo, false, $uid),
            ], 'main');
            return;
        }

        $metrics = DashboardStats::ownerMetrics($uid, true);
        $prevStart = date('Y-m-01', strtotime('first day of last month'));
        $prevEnd = date('Y-m-t', strtotime('last day of last month'));
        $revenuePrevMonth = DashboardStats::revenueBetween($prevStart, $prevEnd);

        View::render('dashboard/index', [
            'title' => Lang::get('nav.dashboard'),
            'isOwner' => true,
            'isPartner' => false,
            'partnerCars' => [],
            'partnerActiveRes' => 0,
            'revenueMonth' => $metrics['revenueMonth'],
            'revenuePrevMonth' => $revenuePrevMonth,
            'revenueDelta' => Formatter::percentDelta($metrics['revenueMonth'], $revenuePrevMonth),
            'fleet' => $metrics['fleet'],
            'activeRes' => $metrics['activeRes'],
            'occupancy' => $metrics['occupancy'],
            'unpaid' => $metrics['unpaid'],
            'chartDays' => $metrics['chartDays'],
            'revenueByCategory' => $metrics['revenueByCategory'],
            'returns' => $metrics['returns'],
            'maintenance' => $metrics['maintenance'],
            'myToday' => [],
            'myTodayCount' => 0,
            'alerts' => DashboardAlerts::collect($pdo, true, null),
        ], 'main');
    }
}
