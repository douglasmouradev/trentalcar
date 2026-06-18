<?php declare(strict_types=1);
/** @var array<int,array{label:string,href?:string}> $crumbs */
if (($crumbs ?? []) === []) {
    return;
}
?>
<nav class="breadcrumbs" aria-label="<?= Lang::e('a11y.breadcrumb') ?>">
  <ol class="breadcrumbs-list">
    <?php foreach ($crumbs as $i => $c): ?>
      <li class="breadcrumbs-item">
        <?php if (!empty($c['href']) && $i < count($crumbs) - 1): ?>
          <a href="<?= htmlspecialchars($c['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php else: ?>
          <span aria-current="page"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
