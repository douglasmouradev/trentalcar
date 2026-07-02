<?php

declare(strict_types=1);

final class SecurityTxtController
{
    public function index(): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=UTF-8');
        }
        $email = trim((string) ($_ENV['SECURITY_CONTACT_EMAIL'] ?? ''));
        if ($email === '') {
            $email = trim((string) ($_ENV['PRIVACY_DPO_EMAIL'] ?? ''));
        }
        if ($email === '') {
            $email = 'security@example.com';
        }
        echo "Contact: mailto:{$email}\n";
        echo 'Expires: ' . gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 year')) . "\n";
        echo "Preferred-Languages: pt, en\n";
    }
}
