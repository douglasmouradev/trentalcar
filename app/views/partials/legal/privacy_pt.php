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
    <section>
        <h2>1. Introdução</h2>
        <p>Este documento descreve como o <strong>Titanium Rental Car</strong> trata dados pessoais em conformidade com a Lei nº 13.709/2018 (LGPD), no contexto do <strong>site público de locação</strong> e do <strong>painel administrativo</strong> (acesso restrito a colaboradores autorizados).</p>
    </section>

    <section>
        <h2>2. Controlador dos dados</h2>
        <p>O controlador é a pessoa jurídica responsável pelas decisões referentes ao tratamento de dados pessoais:</p>
        <ul>
            <li><strong>Razão social / nome fantasia:</strong> <?= $cName !== '' ? htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') : '<em>Preencha em <code>PRIVACY_CONTROLLER_NAME</code> no ambiente (.env).</em>' ?></li>
            <li><strong>EIN (se aplicável):</strong> <?= $cCnpj !== '' ? htmlspecialchars($cCnpj, ENT_QUOTES, 'UTF-8') : '<em>Preencha <code>PRIVACY_CONTROLLER_EIN</code>.</em>' ?></li>
            <li><strong>Endereço:</strong> <?= $addr !== '' ? nl2br(htmlspecialchars($addr, ENT_QUOTES, 'UTF-8')) : '<em>Preencha <code>PRIVACY_ADDRESS</code>.</em>' ?></li>
        </ul>
    </section>

    <section>
        <h2>3. Quais dados coletamos</h2>
        <p><strong>Painel (colaboradores):</strong> nome, e-mail corporativo, perfil de acesso, preferência de idioma, registros de auditoria necessários à segurança e trilhas operacionais (ex.: alterações em cadastros).</p>
        <p><strong>Operação (clientes e reservas):</strong> dados cadastrados pela sua organização para gestão de locação (nome, contacto, documentos que a loja decidir registrar, reservas, veículos, custos e notas operacionais).</p>
        <p><strong>Site público / landing:</strong> dados enviados voluntariamente em formulários de contacto ou reserva (ex.: local, datas, canal WhatsApp/e-mail), e dados técnicos de conexão (logs de servidor, IP, user-agent, quando aplicável à segurança).</p>
    </section>

    <section>
        <h2>4. Finalidades e bases legais</h2>
        <ul>
            <li><strong>Execução de contrato e procedimentos preliminares</strong> (art. 7º, V) — prestação do serviço de software e de locação.</li>
            <li><strong>Obrigação legal / regulamentar</strong> (art. 7º, II) — faturação, impostos e exigências legais da locadora.</li>
            <li><strong>Legítimo interesse</strong> (art. 7º, IX) — segurança da informação, prevenção a fraudes, melhoria técnica e métricas agregadas, sempre com balanceamento e minimização.</li>
            <li><strong>Consentimento</strong> (art. 7º, I), quando exigido — ex.: aceitação explícita em fluxos específicos ou cookies não essenciais (hoje limitamos a cookies essenciais; ver secção 6).</li>
        </ul>
    </section>

    <section id="cookies">
        <h2>5. Cookies e tecnologias similares</h2>
        <p>Utilizamos <strong>cookies de sessão</strong> e armazenamento estritamente necessário para autenticação, proteção CSRF e preferência de idioma no painel. O aviso de cookies no site regista apenas a sua escolha de “aceitar comunicar” (armazenamento local) para não voltar a mostrar a faixa — sem perfilização publicitária.</p>
        <p>Pode limpar cookies e dados de sites nas definições do navegador; note que o login deixará de persistir até nova autenticação.</p>
    </section>

    <section>
        <h2>6. Compartilhamento</h2>
        <p>Não vendemos dados pessoais. O compartilhamento limita-se a: (i) prestadores que a sua organização contratar (hospedagem, e-mail, backups), sob contrato e necessidade; (ii) obrigações legais; (iii) proteção de direitos em processos judiciais ou administrativos.</p>
    </section>

    <section>
        <h2>7. Prazo de conservação</h2>
        <p>Conservamos dados pelo tempo necessário às finalidades descritas, às obrigações legais e à resolução de litígios. Registos de auditoria podem ser mantidos por período superior por motivos de segurança e conformidade, conforme política interna da controladora.</p>
    </section>

    <section>
        <h2>8. Direitos do titular (art. 18 LGPD)</h2>
        <p>Pode solicitar confirmação de tratamento, acesso, correção, anonimização, portabilidade (quando aplicável), eliminação, informação sobre partilhas e revogação de consentimento, respeitados segredos comercial e industrial e bases legais que mantenham o tratamento.</p>
        <p><strong>Canal sugerido:</strong>
            <?php if ($dpoEmail !== ''): ?>
                <a href="mailto:<?= htmlspecialchars($dpoEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($dpoEmail, ENT_QUOTES, 'UTF-8') ?></a><?= $dpoPhone !== '' ? ' · ' . htmlspecialchars($dpoPhone, ENT_QUOTES, 'UTF-8') : '' ?>
            <?php else: ?>
                <em>Preencha <code>PRIVACY_DPO_EMAIL</code> no .env com o e-mail do encarregado/DPO ou canal de privacidade.</em>
            <?php endif; ?>
        </p>
        <p>Também pode contactar a <a href="https://www.gov.br/anpd/pt-br" rel="noopener noreferrer">Autoridade Nacional de Proteção de Dados (ANPD)</a>.</p>
    </section>

    <section>
        <h2>9. Segurança</h2>
        <p>Aplicamos medidas técnicas e organizativas razoáveis (controlo de acesso, HTTPS em produção, cabeçalhos de segurança, tokens CSRF em formulários, limitação de tentativas de login). Nenhum sistema é isento de risco; em caso de incidente relevante, seguiremos o fluxo legal de comunicação.</p>
    </section>

    <section>
        <h2>10. Alterações</h2>
        <p>Esta política pode ser atualizada. A data de vigência será ajustada no topo desta página quando houver mudança material.</p>
    </section>
</article>
