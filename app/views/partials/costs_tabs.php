<?php

declare(strict_types=1);

/** @var string $activeTab */
$activeTab = $activeTab ?? 'monthly';
?>
<div class="tabs mt" role="tablist" aria-label="<?= Lang::e('nav.costs_section') ?>">
    <a class="tab<?= $activeTab === 'monthly' ? ' active' : '' ?>"
       role="tab"
       aria-selected="<?= $activeTab === 'monthly' ? 'true' : 'false' ?>"
       href="<?= Router::url('/monthly-costs') ?>"><?= Lang::e('nav.monthly_costs') ?></a>
    <a class="tab<?= $activeTab === 'fixed' ? ' active' : '' ?>"
       role="tab"
       aria-selected="<?= $activeTab === 'fixed' ? 'true' : 'false' ?>"
       href="<?= Router::url('/fixed-costs') ?>"><?= Lang::e('nav.fixed_costs') ?></a>
</div>
