<?php

declare(strict_types=1);

final class FixedCostController
{
    public function index(): void
    {
        $costs = FixedCost::get();
        View::render('fixed_costs/index', [
            'title' => Lang::get('nav.fixed_costs'),
            'costs' => $costs,
            'fields' => FixedCost::availableFields(),
            'total' => FixedCost::total($costs),
            'canEdit' => Auth::isOwner(),
        ], 'main');
    }

    public function update(): void
    {
        if (!Auth::isOwner()) {
            http_response_code(403);
            Flash::error(Lang::get('error.403_title'));
            Redirect::to('/fixed-costs');
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            Redirect::to('/fixed-costs');
        }
        if (!Schema::hasTable('fixed_costs')) {
            Flash::error(Lang::get('flash.error'));
            Redirect::to('/fixed-costs');
        }

        $amounts = [];
        foreach (FixedCost::availableFields() as $field) {
            $amounts[$field] = max(0.0, (float) str_replace(',', '.', (string) ($_POST[$field] ?? '0')));
        }
        FixedCost::update($amounts);
        Flash::success(Lang::get('fixed_costs.saved'));
        Redirect::to('/fixed-costs');
    }
}
