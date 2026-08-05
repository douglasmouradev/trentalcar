<?php declare(strict_types=1); ?>
<?php
$diffItems = [
    ['key' => '1', 'icon' => 'queue'],
    ['key' => '2', 'icon' => 'iof'],
    ['key' => '3', 'icon' => 'deposit'],
    ['key' => '4', 'icon' => 'nocharge'],
    ['key' => '5', 'icon' => 'sunpass'],
    ['key' => '6', 'icon' => 'pt'],
    ['key' => '7', 'icon' => 'clock'],
    ['key' => '8', 'icon' => 'map'],
    ['key' => '9', 'icon' => 'video'],
    ['key' => '10', 'icon' => 'whatsapp'],
    ['key' => '11', 'icon' => 'shop'],
    ['key' => '12', 'icon' => 'airport'],
    ['key' => '13', 'icon' => 'delivery'],
    ['key' => '14', 'icon' => 'fuel'],
];
$icons = [
    'queue' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M19 8v6"/><path d="M22 11h-6"/>',
    'iof' => '<path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="m4 4 16 16"/>',
    'deposit' => '<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>',
    'nocharge' => '<circle cx="12" cy="12" r="10"/><path d="M16 8l-8 8"/><path d="M8.5 8H16v7.5"/>',
    'sunpass' => '<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h4"/><path d="M14 10h4"/><path d="M6 14h12"/>',
    'pt' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8"/><path d="M8 13h5"/>',
    'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'map' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'video' => '<path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2"/><path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
    'whatsapp' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M9.5 10.5c.5 1.5 2 3 3.5 3.5"/><path d="M14 9.5c.6.3 1.2.8 1.5 1.5"/>',
    'shop' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
    'airport' => '<path d="M2 12h5l2-5h6l2 5h5"/><path d="M7 12v5"/><path d="M17 12v5"/><path d="M5 17h14"/><path d="M12 3v4"/><path d="m9 5 3-2 3 2"/>',
    'delivery' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
    'fuel' => '<path d="M3 22h12"/><path d="M4 9h10"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2h0a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/>',
];
?>
<section class="lp-section lp-section--muted" id="vantagens" data-reveal>
  <div class="lp-section--wide">
    <header class="lp-section-head lp-section-head--center">
      <span class="lp-section-eyebrow"><?= Lang::e('landing.diff_eyebrow') ?></span>
      <h2><?= Lang::e('landing.diff_title') ?></h2>
    </header>
    <ul class="lp-diff-grid">
      <?php foreach ($diffItems as $item): ?>
        <li class="lp-diff-card">
          <span class="lp-diff-icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$item['icon']] ?></svg>
          </span>
          <p><?= Lang::e('landing.diff_' . $item['key']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="lp-diff-cta">
      <a class="btn btn-primary btn-lg" href="#reserva"><?= Lang::e('landing.diff_cta') ?></a>
    </div>
  </div>
</section>
