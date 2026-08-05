<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        if (Auth::check()) {
            Redirect::to('/dashboard');
        }

        $landingOff = !LandingMode::isEnabled();
        if ($landingOff) {
            Redirect::to('/login');
        }

        header('Content-Type: text/html; charset=UTF-8');
        $lead = (string) ($_GET['lead'] ?? '');
        $leadBanner = match ($lead) {
            '1' => 'ok',
            'limite' => 'limite',
            'erro' => 'erro',
            default => null,
        };

        $leadOld = $_SESSION['lead_form_old'] ?? [];
        unset($_SESSION['lead_form_old']);
        $leadErrors = $_SESSION['lead_form_errors'] ?? [];
        unset($_SESSION['lead_form_errors']);
        if (!is_array($leadOld)) {
            $leadOld = [];
        }
        if (!is_array($leadErrors)) {
            $leadErrors = [];
        }

        if (!empty($_GET['inicio']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['inicio'])) {
            $leadOld['inicio'] = (string) $_GET['inicio'];
        }
        if (!empty($_GET['fim']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['fim'])) {
            $leadOld['fim'] = (string) $_GET['fim'];
        }
        $localGet = trim((string) ($_GET['local'] ?? ''));
        if ($localGet !== '' && LeadPickupOptions::isValid($localGet)) {
            $leadOld['local'] = $localGet;
        }
        $hotelGet = trim((string) ($_GET['hotel_nome'] ?? ''));
        if ($hotelGet !== '' && strlen($hotelGet) <= 120) {
            $leadOld['hotel_nome'] = $hotelGet;
        }
        if (!empty($_GET['car_id'])) {
            $leadOld['car_id'] = (int) $_GET['car_id'];
        }

        $carId = (int) ($leadOld['car_id'] ?? 0);
        $selectedCar = $carId > 0 ? Car::find($carId) : null;

        View::render('landing.page', [
            'title' => Lang::get('landing.meta_title'),
            'lead_banner' => $leadBanner,
            'fleetCars' => Car::forPublicLanding(12),
            'leadOld' => $leadOld,
            'leadErrors' => $leadErrors,
            'selectedCar' => $selectedCar,
            'leadWhatsappUrl' => $_SESSION['lead_whatsapp_url'] ?? null,
        ], 'bare');
        unset($_SESSION['lead_whatsapp_url']);
    }
}
