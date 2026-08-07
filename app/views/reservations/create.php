<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $cars */
/** @var array<int,array<string,mixed>> $locations */
/** @var array<int,array<string,mixed>> $customers */
/** @var array<string,mixed>|null $reservation */
/** @var array<string,mixed>|null $leadPrefill */
?>
<?php View::partial('partials/breadcrumbs', [
    'crumbs' => [
        ['label' => Lang::get('nav.dashboard'), 'href' => Router::url('/dashboard')],
        ['label' => Lang::get('nav.reservations'), 'href' => Router::url('/reservations')],
        ['label' => Lang::get('reservation.create')],
    ],
]); ?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('reservation.create') ?></h1>
    <a class="btn btn-secondary" href="<?= Router::url('/reservations') ?>"><?= Lang::e('actions.back') ?></a>
</div>
<?php if (!empty($leadPrefill)): ?>
<div class="card lead-prefill-banner" role="status" style="margin-bottom:1rem;border-left:4px solid var(--primary)">
    <strong><?= Lang::e('reservation.lead_banner') ?></strong>
    <?php if (!empty($leadPrefill['customer_name'])): ?>
        <span><?= htmlspecialchars((string) $leadPrefill['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php if (!empty($leadPrefill['customer_email'])): ?>
            · <?= htmlspecialchars((string) $leadPrefill['customer_email'], ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
        <?php if (!empty($leadPrefill['customer_phone'])): ?>
            · <?= htmlspecialchars((string) $leadPrefill['customer_phone'], ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
    <?php endif; ?>
    <p class="muted"><?= Lang::e('reservation.lead_quick_hint') ?></p>
</div>
<?php endif; ?>
<form class="card form-stack" method="post" action="<?= Router::url('/reservations') ?>" id="resForm"
      data-exclude-id=""
      data-conflict-text="<?= htmlspecialchars(Lang::get('reservation.conflict'), ENT_QUOTES, 'UTF-8') ?>"
      data-conflict-url="<?= htmlspecialchars(Router::url('/api/reservations/conflict'), ENT_QUOTES, 'UTF-8') ?>"
      data-search-url="<?= htmlspecialchars(Router::url('/api/customers/search'), ENT_QUOTES, 'UTF-8') ?>"
      data-quick-url="<?= htmlspecialchars(Router::url('/api/customers/quick'), ENT_QUOTES, 'UTF-8') ?>"
      data-label-submitting="<?= htmlspecialchars(Lang::get('reservation.submitting'), ENT_QUOTES, 'UTF-8') ?>"
      data-days-label="<?= htmlspecialchars(Lang::get('landing.summary_days'), ENT_QUOTES, 'UTF-8') ?>"
      <?php if (!empty($leadPrefill['customer_name'])): ?>
      data-lead-name="<?= htmlspecialchars((string) $leadPrefill['customer_name'], ENT_QUOTES, 'UTF-8') ?>"
      data-lead-email="<?= htmlspecialchars((string) ($leadPrefill['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      data-lead-phone="<?= htmlspecialchars((string) ($leadPrefill['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      <?php endif; ?>>
    <?= Csrf::field() ?>
    <?php View::partial('reservations/form_fields', [
        'cars' => $cars,
        'locations' => $locations,
        'customers' => $customers,
        'r' => $reservation,
        'leadPrefill' => $leadPrefill ?? null,
    ]); ?>
    <button class="btn btn-primary" type="submit" id="resSubmitBtn"><?= Lang::e('actions.save') ?></button>
</form>
<script src="<?= htmlspecialchars(Router::url('/js/reservation-form.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
