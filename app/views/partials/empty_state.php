<?php
declare(strict_types=1);
/** @var string $titleKey @var string $leadKey @var string|null $ctaUrl @var string|null $ctaKey @var string|null $icon */
$iconClass = match ($icon ?? '') {
    'cars' => 'empty-state-icon--cars',
    'calendar' => 'empty-state-icon--calendar',
    'users' => 'empty-state-icon--users',
    default => '',
};
?>
<div class="empty-state card">
    <div class="empty-state-icon <?= htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></div>
    <h2 class="empty-state-title"><?= Lang::e($titleKey) ?></h2>
    <p class="muted empty-state-lead"><?= Lang::e($leadKey) ?></p>
    <?php if (!empty($ctaUrl) && !empty($ctaKey)): ?>
        <p class="empty-state-cta"><a class="btn btn-primary" href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e($ctaKey) ?></a></p>
    <?php endif; ?>
</div>
