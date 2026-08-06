<?php

declare(strict_types=1);

final class BookingController
{
    public function index(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $carId = (int) ($_GET['car'] ?? 0);
        $inicio = trim((string) ($_GET['inicio'] ?? ''));
        $fim = trim((string) ($_GET['fim'] ?? ''));
        $local = trim((string) ($_GET['local'] ?? ''));
        $hotelNome = trim((string) ($_GET['hotel_nome'] ?? ''));

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

        if ($inicio !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) {
            $leadOld['inicio'] = $inicio;
        }
        if ($fim !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fim)) {
            $leadOld['fim'] = $fim;
        }
        if ($local !== '' && LeadPickupOptions::isValid($local)) {
            $leadOld['local'] = $local;
        }
        if ($hotelNome !== '' && strlen($hotelNome) <= 120) {
            $leadOld['hotel_nome'] = $hotelNome;
        }
        if ($carId > 0) {
            $leadOld['car_id'] = $carId;
        }

        $cars = Car::forPublicLanding();
        if ($inicio !== '' && $fim !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fim)) {
            $cars = array_values(array_filter($cars, static fn (array $c): bool =>
                Car::isAvailableForDates((int) $c['id'], $inicio, $fim) || (int) $c['id'] === $carId
            ));
        }

        $selectedCarId = (int) ($leadOld['car_id'] ?? $carId);
        $selectedCar = $selectedCarId > 0 ? Car::find($selectedCarId) : null;
        $leadWhatsappUrl = $_SESSION['lead_whatsapp_url'] ?? null;
        unset($_SESSION['lead_whatsapp_url']);

        View::render('booking/index', [
            'title' => Lang::get('booking.title'),
            'cars' => $cars,
            'selectedCarId' => $selectedCarId,
            'selectedCar' => $selectedCar,
            'inicio' => $inicio,
            'fim' => $fim,
            'local' => (string) ($leadOld['local'] ?? $local),
            'hotelNome' => (string) ($leadOld['hotel_nome'] ?? $hotelNome),
            'lead_banner' => $leadBanner,
            'leadOld' => $leadOld,
            'leadErrors' => $leadErrors,
            'leadWhatsappUrl' => $leadWhatsappUrl,
        ], 'bare');
    }
}