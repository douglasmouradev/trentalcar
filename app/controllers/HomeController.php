<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        if (Auth::check()) {
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $landingEnv = isset($_ENV['APP_LANDING']) ? strtolower(trim((string) $_ENV['APP_LANDING'])) : 'true';
        $landingOff = in_array($landingEnv, ['0', 'false', 'no', 'off'], true);
        if ($landingOff) {
            header('Location: ' . Router::url('/login'));
            exit;
        }

        header('Content-Type: text/html; charset=UTF-8');
        $lead = (string) ($_GET['lead'] ?? '');
        $leadBanner = match ($lead) {
            '1', 'ok' => 'ok',
            'limite' => 'limite',
            'erro' => 'erro',
            default => null,
        };
        View::render('landing.page', [
            'title' => Lang::get('landing.meta_title'),
            'lead_banner' => $leadBanner,
        ], 'bare');
    }
}
