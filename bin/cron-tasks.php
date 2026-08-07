#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require __DIR__ . '/bootstrap-cli.php';

passthru('php ' . escapeshellarg(BASE_PATH . '/bin/rotate-logs.php'), $r1);
passthru('php ' . escapeshellarg(BASE_PATH . '/bin/process-mail.php'), $r2);

$hour = (int) date('G');
if ($hour === 3) {
    passthru('php ' . escapeshellarg(BASE_PATH . '/bin/backup.php'), $r3);
}

$ratePurged = 0;
$tokenPurged = 0;
$leadPurged = 0;
$fxOk = false;
try {
    $ratePurged = DbMaintenance::purgeExpiredRateLimits();
    $tokenPurged = DbMaintenance::purgePasswordResetTokens();
    $leadPurged = LeadJsonlFallback::purgeExpired();
    // Força refresh quando o cache horário expirou (APIs USD→BRL)
    $fxOk = ExchangeRate::refresh(false);
} catch (Throwable $e) {
    AppLog::error('cron.cleanup_failed', ['error' => $e->getMessage()]);
}

echo json_encode([
    'rotate' => $r1,
    'mail' => $r2,
    'backup_skipped' => $hour !== 3,
    'rate_limits_purged' => $ratePurged,
    'reset_tokens_purged' => $tokenPurged,
    'lead_jsonl_purged' => $leadPurged,
    'usd_brl_refreshed' => $fxOk,
    'usd_brl_rate' => ExchangeRate::rate(),
], JSON_PRETTY_PRINT) . PHP_EOL;
