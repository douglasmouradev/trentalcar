<?php declare(strict_types=1);
/** @var callable(string): string $asset */
/** @var string|null $leadWhatsappUrl */
$wa = trim((string) ($leadWhatsappUrl ?? ''));
if ($wa === '') {
    $wa = Contact::whatsappUrl();
}
?>
<div class="lp-lead-success" id="lead-success" role="status">
  <p class="lp-lead-success-text"><?= Lang::e('landing.lead_success_text') ?></p>
  <div class="lp-lead-success-actions">
    <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.lead_whatsapp') ?></a>
  </div>
</div>
