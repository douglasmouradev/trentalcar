# Deploy em produção — Titanium Rental Car

Checklist antes de expor o sistema à internet.

## 1. Variáveis de ambiente (`.env`)

| Variável | Valor em produção |
|----------|-------------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL HTTPS completa |
| `APP_KEY` | 64 chars hex (`php -r "echo bin2hex(random_bytes(32));"`) |
| `HEALTH_TOKEN` | Token aleatório longo |
| `SESSION_SECURE` | `true` |
| `DB_*` | Credenciais do MySQL de produção |
| `PRIVACY_DPO_EMAIL` | E-mail do encarregado LGPD |
| `SECURITY_CONTACT_EMAIL` | E-mail para reporte de segurança |
| `PRIVACY_*` / `BUSINESS_*` / `CONTACT_*` | Dados reais da empresa |
| `MAIL_SMTP_*` | SMTP transaccional com SPF/DKIM |
| `ALLOW_DEMO_LOGIN` | `false` (nunca `true` em prod real) |

O arranque **bloqueia** automaticamente se alguma obrigatória faltar (`ProductionGuard`).

## 2. Servidor web

- **DocumentRoot** = pasta `public/` apenas
- HTTPS obrigatório (Let's Encrypt ou certificado comercial)
- PHP 8.3+ com `pdo_mysql`, `json`, `fileinfo`, `gd`, `openssl`, `sodium`

## 3. Base de dados

```bash
php bin/migrate.php
```

Não execute `database/seed.sql` nem `bin/seed.php` em produção.

## 4. Cron (a cada 5 minutos)

```cron
*/5 * * * * cd /caminho/titanium-rental-car && php bin/cron-tasks.php >> storage/logs/cron.log 2>&1
```

O script `cron-tasks.php` roda: rotação de logs, fila de e-mail e backup diário (03:00).

Backup manual:

```bash
php bin/backup.php
```

Ficheiros em `storage/backups/` (últimos 7 mantidos).

## 5. Health check

```
GET /health?token=SEU_HEALTH_TOKEN
```

Configure monitorização (Uptime Kuma, Pingdom, etc.) para alertar se `status != ok`.

## 6. Pós-deploy

- [ ] Login com conta real (não demo)
- [ ] 2FA activado para owner
- [ ] Formulário de lead envia e-mail (HTML + texto)
- [ ] `/privacidade` e `/termos` com dados correctos
- [ ] `/.well-known/security.txt` acessível
- [ ] `php bin/verify-backup.php` após primeiro backup
- [ ] Webhook de monitorização (`MONITORING_WEBHOOK_URL`) se usar Slack/Discord
- [ ] Testar exportação e anonimização LGPD (perfil owner → cliente)

## 7. LGPD operacional

- **Exportar:** ficha do cliente → «Exportar dados (LGPD)»
- **Anonimizar:** remove PII, mantém reservas históricas; bloqueado se houver reservas activas/confirmadas
- Migration `016_customer_anonymized.sql` obrigatória

## 8. Rollback

1. Restaurar código anterior (git tag)
2. `php bin/migrate.php` (migrations são forward-only; evite downgrades de schema). Prefixos duplicados e ordem: `database/migrations/README.md`.
3. Restaurar BD se necessário: `mysql ... < storage/backups/db-YYYYMMDD.sql`

## 9. Docker

```bash
docker compose up --build -d
```

Serviços: `app` (Apache), `worker` (cron mail/logs), `db` (MySQL).

Para produção, sobrescreva variáveis via ficheiro `.env` ou secrets do orchestrator — **não** use `APP_DEBUG=true`.
