<?php declare(strict_types=1);
$home = Auth::isPartner() ? '/partner/profile' : '/dashboard';
?>
<div class="empty-state card">
    <h1 class="page-title"><?= Lang::e('error.403_title') ?></h1>
    <p style="margin-top:1.25rem"><a class="btn btn-primary" href="<?= Router::url($home) ?>"><?= Lang::e('nav.dashboard') ?></a></p>
</div>
