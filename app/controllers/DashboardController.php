<?php

declare(strict_types=1);

final class DashboardController
{
    public function index(): void
    {
        $isOwner = Auth::isOwner();
        $uid = Auth::id() ?? 0;

        if (Auth::isPartner()) {
            $ids = Auth::partnerCarIds();
            $partnerCars = $ids === [] ? [] : Car::search(['restrict_to_car_ids' => $ids]);
            View::render('dashboard/index', [
                'title' => Lang::get('nav.dashboard'),
                'isOwner' => false,
                'isPartner' => true,
                'partnerCars' => $partnerCars,
                'partnerActiveRes' => DashboardStats::partnerActiveReservations($ids),
                'revenueMonth' => 0.0,
                'fleet' => 0,
                'activeRes' => 0,
                'occupancy' => 0,
                'unpaid' => 0,
                'chartDays' => [],
                'revenueByCategory' => [],
                'returns' => [],
                'maintenance' => [],
                'myToday' => [],
                'myTodayCount' => 0,
            ], 'main');
            return;
        }

        $m = DashboardStats::ownerMetrics($uid, $isOwner);
        View::render('dashboard/index', [
            'title' => Lang::get('nav.dashboard'),
            'isOwner' => $isOwner,
            'isPartner' => false,
            'partnerCars' => [],
            'partnerActiveRes' => 0,
            'revenueMonth' => $m['revenueMonth'],
            'fleet' => $m['fleet'],
            'activeRes' => $m['activeRes'],
            'occupancy' => $m['occupancy'],
            'unpaid' => $m['unpaid'],
            'chartDays' => $m['chartDays'],
            'revenueByCategory' => $m['revenueByCategory'],
            'returns' => $m['returns'],
            'maintenance' => $m['maintenance'],
            'myToday' => $m['myToday'],
            'myTodayCount' => $m['myTodayCount'],
        ], 'main');
    }
}
