<?php
declare(strict_types=1);
/** @var string $current */
$steps = ['pending', 'confirmed', 'active', 'completed'];
$currentIdx = array_search($current, $steps, true);
if ($current === 'cancelled') {
    $steps = ['pending', 'cancelled'];
    $currentIdx = 1;
}
?>
<ol class="status-timeline" aria-label="<?= Lang::e('reservation.timeline') ?>">
    <?php foreach ($steps as $i => $step): ?>
        <?php
        $done = $currentIdx !== false && $i <= $currentIdx;
        $active = $currentIdx === $i;
        ?>
        <li class="status-step<?= $done ? ' is-done' : '' ?><?= $active ? ' is-current' : '' ?>">
            <span class="status-dot"></span>
            <span class="status-label"><?= Lang::e('status.' . $step) ?></span>
        </li>
    <?php endforeach; ?>
</ol>
