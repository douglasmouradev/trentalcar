<?php declare(strict_types=1); /** @var array<string,mixed> $r */ ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('reservation.edit') ?></h1>
    <a class="btn btn-secondary" href="<?= Router::url('/reservations/' . (int) $r['id']) ?>"><?= Lang::e('actions.back') ?></a>
</div>
<form class="card form-stack" method="post" action="<?= Router::url('/reservations/' . (int) $r['id'] . '/update') ?>" id="resForm"
      data-exclude-id="<?= (int) $r['id'] ?>"
      data-conflict-text="<?= htmlspecialchars(Lang::get('reservation.conflict'), ENT_QUOTES, 'UTF-8') ?>"
      data-conflict-url="<?= htmlspecialchars(Router::url('/api/reservations/conflict'), ENT_QUOTES, 'UTF-8') ?>"
      data-search-url="<?= htmlspecialchars(Router::url('/api/customers/search'), ENT_QUOTES, 'UTF-8') ?>"
      data-quick-url="<?= htmlspecialchars(Router::url('/api/customers/quick'), ENT_QUOTES, 'UTF-8') ?>">
    <?= Csrf::field() ?>
    <?php View::partial('reservations/form_fields', ['cars' => $cars, 'locations' => $locations, 'customers' => $customers, 'r' => $r]); ?>
    <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
</form>
<script src="<?= htmlspecialchars(Router::url('/js/reservation-form.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
