<?php

declare(strict_types=1);

final class MonthlyCostController
{
    public function index(): void
    {
        $cars = Car::allWithMonthlyExpenses();
        $categoryTotals = Car::monthlyExpenseCategoryTotals($cars);
        $fleetTotal = 0.0;
        foreach ($categoryTotals as $amount) {
            $fleetTotal += $amount;
        }

        $selectedId = (int) ($_GET['car_id'] ?? 0);
        $selectedCar = null;
        if ($selectedId > 0) {
            $selectedCar = Car::find($selectedId);
        }
        if ($selectedCar === null && $cars !== []) {
            $selectedCar = $cars[0];
            $selectedId = (int) ($selectedCar['id'] ?? 0);
        }

        View::render('monthly_costs/index', [
            'title' => Lang::get('nav.monthly_costs'),
            'cars' => $cars,
            'categoryTotals' => $categoryTotals,
            'fleetTotal' => $fleetTotal,
            'fields' => Car::monthlyExpenseFields(),
            'selectedCar' => $selectedCar,
            'selectedId' => $selectedId,
            'canEdit' => Auth::isOwner(),
        ], 'main');
    }

    public function update(): void
    {
        if (!Auth::isOwner()) {
            http_response_code(403);
            Flash::error(Lang::get('error.403_title'));
            Redirect::to('/monthly-costs');
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            Redirect::to('/monthly-costs');
        }

        $carId = (int) ($_POST['car_id'] ?? 0);
        $car = $carId > 0 ? Car::find($carId) : null;
        if ($car === null) {
            Flash::error(Lang::get('monthly_costs.car_required'));
            Redirect::to('/monthly-costs');
        }

        $amounts = [];
        foreach (Car::monthlyExpenseFields() as $field) {
            $amounts[$field] = max(0.0, (float) str_replace(',', '.', (string) ($_POST[$field] ?? '0')));
        }
        Car::updateMonthlyExpenses($carId, $amounts);
        Flash::success(Lang::get('monthly_costs.saved'));
        Redirect::to('/monthly-costs?car_id=' . $carId);
    }
}
