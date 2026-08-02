<?php

declare(strict_types=1);

/** @var array<string, string> $privacy */
$cName = $privacy['controller_name'] ?? '';
$cCnpj = $privacy['controller_cnpj'] ?? '';
$addr = $privacy['address'] ?? '';
$dpoEmail = $privacy['dpo_email'] ?? '';
$dpoPhone = $privacy['dpo_phone'] ?? '';
?>
<article class="legal-doc">
    <p class="legal-lang-note"><strong>Note:</strong> This English summary is for convenience. Where it conflicts with the Portuguese version or applicable law, the Portuguese policy and Brazilian law prevail.</p>

    <section>
        <h2>1. Introduction</h2>
        <p>This policy explains how <strong>Titanium Rental Car</strong> processes personal data under Brazil’s LGPD (Law 13.709/2018), for both the <strong>public rental website</strong> and the <strong>restricted staff panel</strong>.</p>
    </section>

    <section>
        <h2>2. Controller</h2>
        <ul>
            <li><strong>Name:</strong> <?= $cName !== '' ? htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') : '<em>Set <code>PRIVACY_CONTROLLER_NAME</code> in .env.</em>' ?></li>
            <li><strong>EIN (if any):</strong> <?= $cCnpj !== '' ? htmlspecialchars($cCnpj, ENT_QUOTES, 'UTF-8') : '<em>Set <code>PRIVACY_CONTROLLER_EIN</code>.</em>' ?></li>
            <li><strong>Address:</strong> <?= $addr !== '' ? nl2br(htmlspecialchars($addr, ENT_QUOTES, 'UTF-8')) : '<em>Set <code>PRIVACY_ADDRESS</code>.</em>' ?></li>
        </ul>
    </section>

    <section>
        <h2>3. Data we process</h2>
        <p><strong>Staff:</strong> name, work email, role, language preference, audit logs for security and compliance.</p>
        <p><strong>Business data:</strong> customer and reservation data entered by your organization (as data controller for end-customers).</p>
        <p><strong>Website:</strong> data you send via contact/reservation flows; technical logs (IP, user-agent) when needed for security.</p>
    </section>

    <section>
        <h2>4. Legal bases</h2>
        <p>Contract and pre-contractual measures, legal obligations, legitimate interests (security, fraud prevention), and consent where required.</p>
    </section>

    <section id="cookies">
        <h2>5. Cookies</h2>
        <p>We use session cookies and strictly necessary storage for authentication and CSRF protection. The cookie banner stores only your acknowledgement in <code>localStorage</code> so the banner is not shown again — no behavioural advertising.</p>
    </section>

    <section>
        <h2>6. Rights (Art. 18 LGPD)</h2>
        <p>You may request access, correction, anonymization, deletion, portability (where applicable), information on sharing, and withdrawal of consent, subject to legal retention.</p>
        <p><strong>Contact:</strong>
            <?php if ($dpoEmail !== ''): ?>
                <a href="mailto:<?= htmlspecialchars($dpoEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($dpoEmail, ENT_QUOTES, 'UTF-8') ?></a><?= $dpoPhone !== '' ? ' · ' . htmlspecialchars($dpoPhone, ENT_QUOTES, 'UTF-8') : '' ?>
            <?php else: ?>
                <em>Set <code>PRIVACY_DPO_EMAIL</code> in .env.</em>
            <?php endif; ?>
        </p>
    </section>

    <section>
        <h2>7. Security</h2>
        <p>We use access control, security headers, CSRF tokens, and login rate limiting. Use HTTPS in production.</p>
    </section>
</article>
