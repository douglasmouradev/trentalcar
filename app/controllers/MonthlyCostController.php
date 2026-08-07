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

        View::render('monthly_costs/index', [
            'title' => Lang::get('nav.monthly_costs'),
            'cars' => $cars,
            'categoryTotals' => $categoryTotals,
            'fleetTotal' => $fleetTotal,
            'fields' => Car::monthlyExpenseFields(),
        ], 'main');
    }
}
