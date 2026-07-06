<?php declare(strict_types=1);
$home = '/';
if (Auth::check()) {
    $home = Auth::isPartner() ? '/partner/profile' : '/dashboard';
} else {
    $home = LandingMode::isEnabled() ? '/' : '/login';
}
$homeLabel = Auth::check() ? Lang::get('nav.dashboard') : (strpos($home, 'login') !== false ? Lang::get('error.404_login') : Lang::get('error.404_home'));
?>
<div class="empty-state card">
    <h1 class="page-title"><?= Lang::e('error.404_title') ?></h1>
    <p class="muted"><?= Lang::e('error.404_lead') ?></p>
    <p style="margin-top:1.25rem"><a class="btn btn-primary" href="<?= Router::url($home) ?>"><?= htmlspecialchars($homeLabel, ENT_QUOTES, 'UTF-8') ?></a></p>
</div>
