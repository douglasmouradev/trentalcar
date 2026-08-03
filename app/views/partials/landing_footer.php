<?php declare(strict_types=1);
/** @var callable(string): string $asset */
/** @var bool $compact */
$compact = $compact ?? false;
?>
<footer class="site-footer lp-footer<?= $compact ? ' lp-footer--compact' : '' ?>">
    <?php if (!$compact): ?>
    <div class="lp-footer-grid">
        <div class="lp-footer-brand">
            <strong><?= Lang::e('app.name') ?></strong>
            <p class="lp-footer-legal"><?= htmlspecialchars(Contact::footerLegalLine(), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="lp-footer-hours muted"><?= Lang::e('landing.footer_hours', ['hours' => Contact::businessHours()]) ?></p>
            <p class="lp-footer-contact">
                <a href="tel:<?= htmlspecialchars(Contact::phoneTel(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(Contact::phoneDisplay(), ENT_QUOTES, 'UTF-8') ?></a>
                · <a href="mailto:<?= htmlspecialchars(Contact::email(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(Contact::email(), ENT_QUOTES, 'UTF-8') ?></a>
                · <a href="<?= htmlspecialchars(Contact::instagramUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(Contact::instagramHandle(), ENT_QUOTES, 'UTF-8') ?></a>
            </p>
        </div>
        <div>
            <strong><?= Lang::e('landing.footer_col_book') ?></strong>
            <ul class="lp-footer-links">
                <li><a href="<?= $asset('/reservar') ?>"><?= Lang::e('booking.title') ?></a></li>
                <li><a href="<?= $asset('/consultar') ?>"><?= Lang::e('consult.title') ?></a></li>
                <li><a href="<?= $asset('/') ?>#frota"><?= Lang::e('landing.nav_frota') ?></a></li>
            </ul>
        </div>
        <div>
            <strong><?= Lang::e('landing.footer_col_info') ?></strong>
            <ul class="lp-footer-links">
                <li><a href="<?= $asset('/') ?>#vantagens"><?= Lang::e('landing.nav_vantagens') ?></a></li>
                <li><a href="<?= $asset('/') ?>#faq"><?= Lang::e('landing.nav_faq') ?></a></li>
                <li><a href="<?= htmlspecialchars(Contact::whatsappUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= Lang::e('landing.cta_wa') ?></a></li>
                <li><a href="<?= htmlspecialchars(Contact::instagramUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= Lang::e('landing.cta_instagram') ?></a></li>
            </ul>
        </div>
        <div>
            <strong><?= Lang::e('landing.footer_col_legal') ?></strong>
            <ul class="lp-footer-links">
                <li><a href="<?= $asset('/privacidade') ?>"><?= Lang::e('landing.footer_legal_privacy') ?></a></li>
                <li><a href="<?= $asset('/termos') ?>"><?= Lang::e('landing.footer_legal_terms') ?></a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    <p class="lp-footer-bottom">
        <?php if ($compact): ?>
            <a href="<?= $asset('/privacidade') ?>"><?= Lang::e('landing.footer_legal_privacy') ?></a>
            · <a href="<?= $asset('/termos') ?>"><?= Lang::e('landing.footer_legal_terms') ?></a>
            ·
        <?php endif; ?>
        © <span id="lp-year"><?= date('Y') ?></span> <?= Lang::e('app.name') ?>. <?= Lang::e('landing.footer_rights') ?>
    </p>
</footer>
