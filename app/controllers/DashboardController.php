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



        $pdo = Database::pdo();

        $isOwner = Auth::isOwner();

        $uid = Auth::id();



        if (!$isOwner && $uid !== null) {

            $this->renderOperatorDashboard($pdo, $uid);

            return;

        }



        $monthStart = date('Y-m-01');

        $monthEnd = date('Y-m-t');

        $prevStart = date('Y-m-01', strtotime('first day of last month'));

        $prevEnd = date('Y-m-t', strtotime('last day of last month'));



        $revenueStmt = $pdo->prepare(

            "SELECT COALESCE(SUM(final_amount),0) FROM reservations

             WHERE status IN ('confirmed','active','completed') AND pickup_date BETWEEN ? AND ?"

        );

        $revenueStmt->execute([$monthStart, $monthEnd]);

        $revenueMonth = (float) $revenueStmt->fetchColumn();



        $revenuePrevStmt = $pdo->prepare(

            "SELECT COALESCE(SUM(final_amount),0) FROM reservations

             WHERE status IN ('confirmed','active','completed') AND pickup_date BETWEEN ? AND ?"

        );

        $revenuePrevStmt->execute([$prevStart, $prevEnd]);

        $revenuePrevMonth = (float) $revenuePrevStmt->fetchColumn();

        $revenueDelta = Formatter::percentDelta($revenueMonth, $revenuePrevMonth);



        $fleet = (int) $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();

        $activeRes = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'active'")->fetchColumn();

        $occupancy = $fleet > 0 ? round(($activeRes / $fleet) * 100) : 0;



        $unpaid = (int) $pdo->query(

            "SELECT COUNT(*) FROM reservations WHERE payment_status = 'unpaid' AND status NOT IN ('cancelled','completed')"

        )->fetchColumn();



        $chartDays = [];

        for ($i = 29; $i >= 0; $i--) {

            $d = date('Y-m-d', strtotime("-{$i} days"));

            $chartDays[$d] = 0;

        }

        $stmt = $pdo->query(

            "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM reservations

             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at)"

        );

        foreach ($stmt->fetchAll() as $row) {

            if (isset($chartDays[$row['d']])) {

                $chartDays[$row['d']] = (int) $row['c'];

            }

        }



        $catQ = $pdo->prepare(

            "SELECT car.category, COALESCE(SUM(r.final_amount),0) AS total

             FROM reservations r JOIN cars car ON car.id = r.car_id

             WHERE r.status IN ('confirmed','active','completed') AND r.pickup_date BETWEEN ? AND ?

             GROUP BY car.category"

        );

        $catQ->execute([$monthStart, $monthEnd]);

        $revenueByCategory = $catQ->fetchAll();



        $returns = $pdo->query(

            "SELECT r.*, c.full_name AS customer_name, car.brand, car.model, car.license_plate

             FROM reservations r

             JOIN customers c ON c.id = r.customer_id

             JOIN cars car ON car.id = r.car_id

             WHERE r.status IN ('active','confirmed') AND r.return_date IN (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY))

             ORDER BY r.return_date, r.return_time LIMIT 15"

        )->fetchAll();



        $maintenance = $pdo->query(

            "SELECT * FROM cars WHERE status = 'maintenance' ORDER BY updated_at DESC LIMIT 10"

        )->fetchAll();



        $alerts = DashboardAlerts::collect($pdo, true, null);



        View::render('dashboard/index', [

            'title' => Lang::get('nav.dashboard'),

            'isOwner' => true,

            'isPartner' => false,

            'partnerCars' => [],

            'partnerActiveRes' => 0,

            'revenueMonth' => $revenueMonth,

            'revenuePrevMonth' => $revenuePrevMonth,

            'revenueDelta' => $revenueDelta,

            'fleet' => $fleet,

            'activeRes' => $activeRes,

            'occupancy' => $occupancy,

            'unpaid' => $unpaid,

            'chartDays' => $chartDays,

            'revenueByCategory' => $revenueByCategory,

            'returns' => $returns,

            'maintenance' => $maintenance,

            'myToday' => [],

            'myTodayCount' => 0,

            'alerts' => $alerts,

        ], 'main');

    }



    private static function renderOperatorDashboard(PDO $pdo, int $uid): void

    {

        $t = $pdo->prepare(

            "SELECT COUNT(*) FROM reservations WHERE operator_id = ? AND pickup_date = CURDATE() AND status NOT IN ('cancelled','completed')"

        );

        $t->execute([$uid]);

        $myTodayCount = (int) $t->fetchColumn();



        $list = $pdo->prepare(

            "SELECT r.*, c.full_name AS customer_name, car.brand, car.model

             FROM reservations r

             JOIN customers c ON c.id = r.customer_id

             JOIN cars car ON car.id = r.car_id

             WHERE r.operator_id = ? AND r.pickup_date >= CURDATE() AND r.status NOT IN ('cancelled','completed')

             ORDER BY r.pickup_date LIMIT 10"

        );

        $list->execute([$uid]);

        $myToday = $list->fetchAll();



        $alerts = DashboardAlerts::collect($pdo, false, $uid);



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

            'myToday' => $myToday,

            'myTodayCount' => $myTodayCount,

            'alerts' => $alerts,

        ], 'main');

    }

}

