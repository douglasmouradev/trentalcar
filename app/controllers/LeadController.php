<?php

declare(strict_types=1);

final class LeadController
{
    public function submit(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            header('Location: ' . Router::url('/') . '?lead=erro');
            exit;
        }
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            header('Location: ' . Router::url('/') . '?lead=1#frota');
            exit;
        }
        if (LeadRateLimiter::tooMany()) {
            header('Location: ' . Router::url('/') . '?lead=limite');
            exit;
        }

        $local = trim((string) ($_POST['local'] ?? ''));
        $inicio = trim((string) ($_POST['inicio'] ?? ''));
        $fim = trim((string) ($_POST['fim'] ?? ''));
        $mesmo = isset($_POST['mesmo_local']) ? '1' : '0';
        $localDevolucao = trim((string) ($_POST['local_devolucao'] ?? ''));
        $contactName = trim((string) ($_POST['contact_name'] ?? ''));
        $contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
        $contactPhone = trim((string) ($_POST['contact_phone'] ?? ''));

        if ($local === '' || strlen($local) > 240 || !self::validDate($inicio) || !self::validDate($fim)) {
            header('Location: ' . Router::url('/') . '?lead=erro');
            exit;
        }
        if ($contactName === '' || ($contactEmail === '' && $contactPhone === '')) {
            header('Location: ' . Router::url('/') . '?lead=erro');
            exit;
        }
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . Router::url('/') . '?lead=erro');
            exit;
        }
        if ($mesmo === '0') {
            if ($localDevolucao === '' || strlen($localDevolucao) > 240) {
                header('Location: ' . Router::url('/') . '?lead=erro');
                exit;
            }
        } else {
            $localDevolucao = $local;
        }
        if (strcmp($inicio, $fim) > 0) {
            header('Location: ' . Router::url('/') . '?lead=erro');
            exit;
        }

        $ipHash = hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        $leadId = 0;
        try {
            $leadId = Lead::create([
                'location_text' => $local,
                'start_date' => $inicio,
                'end_date' => $fim,
                'same_location' => (int) $mesmo,
                'return_location_text' => $localDevolucao,
                'contact_name' => $contactName,
                'contact_email' => $contactEmail ?: null,
                'contact_phone' => $contactPhone ?: null,
                'ip_hash' => $ipHash,
                'status' => 'new',
            ]);
        } catch (Throwable $e) {
            AppError::log($e);
            if (ProductionGuard::isProduction()) {
                header('Location: ' . Router::url('/') . '?lead=erro');
                exit;
            }
            self::appendJsonlFallback($local, $inicio, $fim, $mesmo, $localDevolucao, $ipHash, $contactName, $contactEmail, $contactPhone);
        }

        if ($leadId > 0) {
            self::notifyNewLead($leadId, $local, $inicio, $fim, $contactName, $contactEmail, $contactPhone);
        }

        LeadRateLimiter::hit();

        header('Location: ' . Router::url('/') . '?lead=ok#frota');
        exit;
    }

    private static function appendJsonlFallback(
        string $local,
        string $inicio,
        string $fim,
        string $mesmo,
        string $localDevolucao,
        string $ipHash,
        string $contactName = '',
        string $contactEmail = '',
        string $contactPhone = ''
    ): void {
        $dir = BASE_PATH . '/storage/leads';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $line = json_encode([
            'at' => gmdate('c'),
            'local' => $local,
            'inicio' => $inicio,
            'fim' => $fim,
            'mesmo_local' => $mesmo,
            'local_devolucao' => $localDevolucao,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'ip_hash' => $ipHash,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
        file_put_contents($dir . '/leads.jsonl', $line, FILE_APPEND | LOCK_EX);
    }

    private static function notifyNewLead(
        int $leadId,
        string $local,
        string $inicio,
        string $fim,
        string $contactName,
        string $contactEmail,
        string $contactPhone
    ): void {
        $to = trim((string) ($_ENV['MAIL_NOTIFY'] ?? $_ENV['SECURITY_CONTACT_EMAIL'] ?? ''));
        if ($to === '') {
            return;
        }
        try {
            $body = Lang::get('lead.email_body', [
                'id' => (string) $leadId,
                'local' => $local,
                'inicio' => $inicio,
                'fim' => $fim,
                'name' => $contactName,
                'email' => $contactEmail,
                'phone' => $contactPhone,
                'url' => Router::url('/leads'),
            ]);
            Mail::send($to, Lang::get('lead.email_subject'), $body);
        } catch (Throwable $e) {
            AppError::log($e);
        }
    }

    private static function validDate(string $d): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            return false;
        }
        $t = strtotime($d . ' UTC');
        return $t !== false;
    }
}
