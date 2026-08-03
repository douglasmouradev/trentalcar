<?php

declare(strict_types=1);

final class PublicLeadController
{
    public function submit(): void
    {
        $returnUrl = self::resolveReturnUrl();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            self::fail($returnUrl, self::collectOld(), [Lang::get('error.csrf')]);
        }
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            Redirect::toUrl($returnUrl . '?lead=1#reserva');
        }
        if (LeadRateLimiter::tooMany()) {
            Redirect::toUrl($returnUrl . '?lead=limite#reserva');
        }

        $old = self::collectOld();
        $errors = [];

        $name = trim((string) ($old['nome'] ?? ''));
        $email = trim((string) ($old['email'] ?? ''));
        $phone = trim((string) ($old['telefone'] ?? ''));
        $local = trim((string) ($old['local'] ?? ''));
        $inicio = trim((string) ($old['inicio'] ?? ''));
        $fim = trim((string) ($old['fim'] ?? ''));
        $mesmo = isset($_POST['mesmo_local']) ? '1' : '0';
        $localDevolucao = trim((string) ($old['local_devolucao'] ?? ''));
        $carId = (int) ($old['car_id'] ?? 0);
        $old['mesmo_local'] = $mesmo;

        if ($name === '' || strlen($name) > 150) {
            $errors[] = Lang::get('landing.error_name');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = Lang::get('landing.error_email');
        }
        if ($phone === '' || strlen($phone) > 30) {
            $errors[] = Lang::get('landing.error_phone');
        }
        if ($local === '' || !LeadPickupOptions::isValid($local)) {
            $errors[] = Lang::get('landing.error_local');
        }
        if (!self::validDate($inicio) || !self::validDate($fim)) {
            $errors[] = Lang::get('landing.error_date_required');
        } elseif (strcmp($inicio, $fim) > 0) {
            $errors[] = Lang::get('landing.error_date_order');
        }
        if ($mesmo === '0') {
            if ($localDevolucao === '' || !LeadPickupOptions::isValid($localDevolucao)) {
                $errors[] = Lang::get('landing.error_return_local');
            }
        } else {
            $localDevolucao = $local;
        }
        if ($carId > 0 && Car::find($carId) === null) {
            $carId = 0;
            $old['car_id'] = 0;
        }
        if ($carId > 0 && !Car::isAvailableForDates($carId, $inicio, $fim)) {
            $errors[] = Lang::get('landing.error_car_unavailable');
        }

        if ($errors !== []) {
            self::fail($returnUrl, $old, $errors);
        }

        $leadId = 0;
        try {
            $leadId = Lead::create([
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'local' => $local,
                'inicio' => $inicio,
                'fim' => $fim,
                'mesmo_local' => (int) $mesmo,
                'local_devolucao' => $localDevolucao,
                'car_id' => $carId > 0 ? $carId : null,
                'ip_hash' => hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '')),
            ]);
        } catch (Throwable $e) {
            AppLog::error('lead.create_failed', ['error' => $e->getMessage()]);
            try {
                LeadJsonlFallback::append([
                    'full_name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'local' => $local,
                    'inicio' => $inicio,
                    'fim' => $fim,
                    'mesmo_local' => $mesmo,
                    'local_devolucao' => $localDevolucao,
                ]);
            } catch (Throwable $fallbackError) {
                AppLog::error('lead.fallback_failed', ['error' => $fallbackError->getMessage()]);
            }
            self::fail($returnUrl, $old, [Lang::get('landing.lead_erro')]);
        }

        $carLabel = null;
        if ($carId > 0) {
            $car = Car::find($carId);
            if ($car !== null) {
                $carLabel = trim((string) $car['brand'] . ' ' . (string) $car['model']);
            }
        }
        try {
            LeadNotify::sendConfirmation($email, $name, $inicio, $fim, $carLabel);
            if ($leadId > 0) {
                LeadNotify::notifyStaff($leadId, $name, $email, $phone, $inicio, $fim, $carLabel, $local);
            }
        } catch (Throwable $e) {
            AppLog::error('lead.notify_failed', ['error' => $e->getMessage()]);
        }

        $_SESSION['lead_whatsapp_url'] = Contact::whatsappUrl(
            LeadNotify::whatsappMessage($name, $inicio, $fim, $local, $carLabel)
        );

        LeadRateLimiter::hit();
        Redirect::toUrl($returnUrl . '?lead=1#reserva');
    }

    /** @return array<string, mixed> */
    private static function collectOld(): array
    {
        return [
            'nome' => trim((string) ($_POST['nome'] ?? $_POST['full_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'telefone' => trim((string) ($_POST['telefone'] ?? $_POST['phone'] ?? '')),
            'local' => trim((string) ($_POST['local'] ?? '')),
            'inicio' => trim((string) ($_POST['inicio'] ?? '')),
            'fim' => trim((string) ($_POST['fim'] ?? '')),
            'mesmo_local' => isset($_POST['mesmo_local']) ? '1' : '0',
            'local_devolucao' => trim((string) ($_POST['local_devolucao'] ?? '')),
            'car_id' => (int) ($_POST['car_id'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $old
     * @param array<int, string> $errors
     */
    private static function fail(string $returnUrl, array $old, array $errors): never
    {
        $_SESSION['lead_form_old'] = $old;
        $_SESSION['lead_form_errors'] = $errors;
        $sep = str_contains($returnUrl, '?') ? '&' : '?';
        Redirect::toUrl($returnUrl . $sep . 'lead=erro#reserva');
    }

    private static function resolveReturnUrl(): string
    {
        $path = trim((string) ($_POST['_return'] ?? '/'));
        if ($path === '' || $path[0] !== '/' || str_contains($path, '//')) {
            return Router::url('/');
        }
        return Router::url($path);
    }

    private static function validDate(string $d): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            return false;
        }
        return strtotime($d . ' UTC') !== false;
    }
}
