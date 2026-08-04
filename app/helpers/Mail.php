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

        $app = Config::app();
        $from = trim((string) ($_ENV['MAIL_FROM'] ?? 'noreply@titaniumrental.local'));
        $fromName = trim((string) ($_ENV['MAIL_FROM_NAME'] ?? ($app['name'] ?? 'Titanium Rental Car')));
        $env = $app['env'] ?? 'production';
        $smtpHost = trim((string) ($_ENV['MAIL_SMTP_HOST'] ?? ''));

        if ($env !== 'production' && $smtpHost === '') {
            return self::storeDev($to, $subject, $bodyText);
        }

        if ($smtpHost !== '') {
            return self::sendSmtp($to, $subject, $bodyText, $from, $fromName);
        }

        $mime = self::buildMime($bodyText);
        $headers = [
            $mime['headers'],
            'From: ' . self::formatAddress($from, $fromName),
        ];
        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $mime['body'], implode("\r\n", $headers));
        if (!$ok) {
            AppLog::error('mail.send_failed', [
                'to_hash' => hash('sha256', strtolower($to)),
                'subject_len' => strlen($subject),
            ]);
        }
        return $ok;
    }

    public static function queue(string $to, string $subject, string $bodyText): bool
    {
        if (Schema::hasTable('mail_outbox')) {
            MailOutbox::enqueue($to, $subject, $bodyText);
            return true;
        }
        return self::send($to, $subject, $bodyText);
    }

    /** @return array{processed: int, sent: int, failed: int} */
    public static function processOutbox(int $limit = 20): array
    {
        if (!Schema::hasTable('mail_outbox')) {
            return ['processed' => 0, 'sent' => 0, 'failed' => 0];
        }
        $sent = 0;
        $failed = 0;
        foreach (MailOutbox::pending($limit) as $row) {
            $id = (int) $row['id'];
            $ok = self::send((string) $row['to_email'], (string) $row['subject'], (string) $row['body']);
            if ($ok) {
                MailOutbox::markSent($id);
                $sent++;
            } else {
                MailOutbox::markFailed($id, 'send returned false');
                $failed++;
            }
        }
        return ['processed' => $sent + $failed, 'sent' => $sent, 'failed' => $failed];
    }

    private static function sendSmtp(string $to, string $subject, string $body, string $from, string $fromName): bool
    {
        $host = trim((string) ($_ENV['MAIL_SMTP_HOST'] ?? ''));
        $port = (int) ($_ENV['MAIL_SMTP_PORT'] ?? 587);
        $user = trim((string) ($_ENV['MAIL_SMTP_USER'] ?? ''));
        $pass = (string) ($_ENV['MAIL_SMTP_PASS'] ?? '');
        $secure = strtolower(trim((string) ($_ENV['MAIL_SMTP_SECURE'] ?? 'tls')));
        // aaPanel/self-hosted often uses cert that fails OpenSSL verify; set MAIL_SMTP_SSL_VERIFY=false
        $verifySsl = filter_var($_ENV['MAIL_SMTP_SSL_VERIFY'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $ctxLabel = "{$host}:{$port}/{$secure}";

        $sslOpts = [
            'peer_name' => $host,
            'verify_peer' => $verifySsl,
            'verify_peer_name' => $verifySsl,
            'allow_self_signed' => !$verifySsl,
        ];
        $ctx = stream_context_create(['ssl' => $sslOpts]);

        $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
        $fp = @stream_socket_client(
            $remote . ':' . $port,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if ($fp === false) {
            AppError::log(new RuntimeException("SMTP connect failed [{$ctxLabel}]: {$errstr} ({$errno})"));
            return false;
        }

        stream_set_timeout($fp, 15);
        if (!self::smtpExpect($fp, [220], $last)) {
            self::logSmtpStep($ctxLabel, 'banner', $last);
            fclose($fp);
            return false;
        }
        $ehloHost = parse_url(Config::app()['url'] ?? 'http://localhost', PHP_URL_HOST) ?: 'localhost';
        fwrite($fp, "EHLO {$ehloHost}\r\n");
        if (!self::smtpExpect($fp, [250], $last)) {
            self::logSmtpStep($ctxLabel, 'EHLO', $last);
            fclose($fp);
            return false;
        }
        if ($secure === 'tls') {
            fwrite($fp, "STARTTLS\r\n");
            if (!self::smtpExpect($fp, [220], $last)) {
                self::logSmtpStep($ctxLabel, 'STARTTLS', $last);
                fclose($fp);
                return false;
            }
            foreach ($sslOpts as $k => $v) {
                stream_context_set_option($fp, 'ssl', $k, $v);
            }
            if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                AppError::log(new RuntimeException(
                    "SMTP STARTTLS crypto failed [{$ctxLabel}] verify=" . ($verifySsl ? 'true' : 'false')
                    . '. Set MAIL_SMTP_SSL_VERIFY=false or fix the mail SSL cert.'
                ));
                fclose($fp);
                return false;
            }
            fwrite($fp, "EHLO {$ehloHost}\r\n");
            if (!self::smtpExpect($fp, [250], $last)) {
                self::logSmtpStep($ctxLabel, 'EHLO after TLS', $last);
                fclose($fp);
                return false;
            }
        }
        if ($user !== '') {
            fwrite($fp, "AUTH LOGIN\r\n");
            if (!self::smtpExpect($fp, [334], $last)) {
                self::logSmtpStep($ctxLabel, 'AUTH LOGIN', $last);
                fclose($fp);
                return false;
            }
            fwrite($fp, base64_encode($user) . "\r\n");
            if (!self::smtpExpect($fp, [334], $last)) {
                self::logSmtpStep($ctxLabel, 'AUTH user', $last);
                fclose($fp);
                return false;
            }
            fwrite($fp, base64_encode($pass) . "\r\n");
            if (!self::smtpExpect($fp, [235], $last)) {
                self::logSmtpStep($ctxLabel, 'AUTH password', $last);
                fclose($fp);
                return false;
            }
        }
        fwrite($fp, 'MAIL FROM:<' . $from . ">\r\n");
        if (!self::smtpExpect($fp, [250], $last)) {
            self::logSmtpStep($ctxLabel, 'MAIL FROM', $last);
            fclose($fp);
            return false;
        }
        fwrite($fp, 'RCPT TO:<' . $to . ">\r\n");
        if (!self::smtpExpect($fp, [250, 251], $last)) {
            self::logSmtpStep($ctxLabel, 'RCPT TO', $last);
            fclose($fp);
            return false;
        }
        fwrite($fp, "DATA\r\n");
        if (!self::smtpExpect($fp, [354], $last)) {
            self::logSmtpStep($ctxLabel, 'DATA', $last);
            fclose($fp);
            return false;
        }
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mime = self::buildMime($body);
        $payload = 'From: ' . self::formatAddress($from, $fromName) . "\r\n"
            . "To: {$to}\r\n"
            . "Subject: {$encodedSubject}\r\n"
            . $mime['headers'] . "\r\n"
            . "\r\n"
            . str_replace("\n.", "\n..", $mime['body']) . "\r\n.\r\n";
        fwrite($fp, $payload);
        if (!self::smtpExpect($fp, [250], $last)) {
            self::logSmtpStep($ctxLabel, 'DATA body', $last);
            fclose($fp);
            return false;
        }
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }

    private static function logSmtpStep(string $ctx, string $step, string $response): void
    {
        $safe = preg_replace('/[^\x20-\x7E]/', '', mb_substr($response, 0, 200)) ?? '';
        AppError::log(new RuntimeException("SMTP {$step} failed [{$ctx}]: {$safe}"));
    }

    /**
     * @param resource $fp
     * @param list<int> $codes
     */
    private static function smtpExpect($fp, array $codes, ?string &$raw = null): bool
    {
        $line = '';
        while (($chunk = fgets($fp, 515)) !== false) {
            $line .= $chunk;
            if (isset($chunk[3]) && $chunk[3] === ' ') {
                break;
            }
        }
        $raw = $line;
        $code = (int) substr(trim($line), 0, 3);
        return in_array($code, $codes, true);
    }

    /** @return array{headers: string, body: string} */
    private static function buildMime(string $bodyText): array
    {
        $boundary = 'trc_' . bin2hex(random_bytes(8));
        $plain = str_replace(["\r\n", "\r"], "\n", $bodyText);
        $html = self::textToHtml($plain);
        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $plain . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $html . "\r\n"
            . "--{$boundary}--\r\n";
        return [
            'headers' => "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"{$boundary}\"",
            'body' => $body,
        ];
    }

    private static function textToHtml(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escaped = nl2br($escaped, false);
        $appName = htmlspecialchars((string) (Config::app()['name'] ?? 'Titanium Rental Car'), ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.5;color:#0f172a">'
            . '<p style="margin:0 0 1rem">' . $escaped . '</p>'
            . '<p style="margin:0;font-size:12px;color:#64748b">' . $appName . '</p></body></html>';
    }

    private static function formatAddress(string $email, string $name): string
    {
        if ($name === '') {
            return $email;
        }
        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $email . '>';
    }

    private static function storeDev(string $to, string $subject, string $body): bool
    {
        $dir = BASE_PATH . '/storage/mail';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file = $dir . '/' . date('Ymd-His') . '_' . bin2hex(random_bytes(4)) . '.eml';
        $toHash = hash('sha256', strtolower($to));
        $content = "To-Hash: {$toHash}\nSubject: {$subject}\nDate: " . date('c') . "\n\n{$body}\n";
        return file_put_contents($file, $content) !== false;
    }
}
