<?php declare(strict_types=1);
/** @var callable(string): string $asset */
/** @var string|null $leadWhatsappUrl */
$wa = trim((string) ($leadWhatsappUrl ?? ''));
if ($wa === '') {
    $wa = Contact::whatsappUrl();
}
?>
<div class="lp-lead-success" id="lead-success" role="status">
  <div class="lp-lead-success-icon" aria-hidden="true">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/>
      <path d="M8 12.5l2.5 2.5L16 9"/>
    </svg>
  </div>
  <p class="lp-lead-success-text"><?= Lang::e('landing.lead_success_text') ?></p>
  <div class="lp-lead-success-actions">
    <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.lead_whatsapp') ?></a>
  </div>
</div>
