<?php

declare(strict_types=1);

final class Mail
{
    public static function send(string $to, string $subject, string $bodyText): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $driver = strtolower(trim((string) ($_ENV['MAIL_DRIVER'] ?? 'log')));
        $from = trim((string) ($_ENV['MAIL_FROM'] ?? 'noreply@localhost'));
        $fromName = trim((string) ($_ENV['MAIL_FROM_NAME'] ?? Config::app()['name']));

        if ($driver === 'log' || ($driver !== 'smtp' && $driver !== 'mail')) {
            return self::logMail($to, $subject, $bodyText, $from);
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . self::formatAddress($from, $fromName),
            'X-Mailer: Titanium-Rental-Car',
        ];

        return @mail($to, self::encodeSubject($subject), $bodyText, implode("\r\n", $headers));
    }

    private static function logMail(string $to, string $subject, string $body, string $from): bool
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $line = date('c') . "\nTO: {$to}\nFROM: {$from}\nSUBJECT: {$subject}\n---\n{$body}\n\n";
        file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
        return true;
    }

    private static function formatAddress(string $email, string $name): string
    {
        if ($name === '') {
            return $email;
        }
        return sprintf('%s <%s>', self::encodeHeader($name), $email);
    }

    private static function encodeHeader(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }

    private static function encodeSubject(string $subject): string
    {
        return str_replace(["\r", "\n"], '', $subject);
    }
}
