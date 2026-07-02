# Migrations

Aplique sempre com `php bin/migrate.php` após actualizar o código. **Não aplique ficheiros SQL à mão** salvo emergência — ver ordem abaixo.

## Numeração duplicada (histórico)

Existem prefixos partilhados (`006_*`, `007_*`, `008_*`, `009_*`) de evoluções paralelas no mesmo período. Isto é **intencional e válido**: o script ordena por **nome de ficheiro** (`SORT_STRING`) e regista cada ficheiro em `schema_migrations`.

| Prefixo | Ficheiros | Conteúdo |
|---------|-----------|----------|
| `006` | `006_leads_rate_limits_soft_delete.sql` | Leads legado (`location_text`), rate limits, soft delete |
| `006` | `006_must_change_password.sql` | Coluna `users.must_change_password` |
| `007` | `007_reservation_code_length.sql` | `reservations.code` VARCHAR(16) |
| `007` | `007_totp_2fa.sql` | Coluna `users.totp_secret` |
| `008` | `008_leads_contact.sql` | Colunas `contact_*` em leads legado |
| `008` | `008_user_cars_quota.sql` | `user_cars.quota_percent` |
| `009` | `009_fix_demo_password_hash.sql` | Hash demo `password123` |
| `009` | `009_password_reset_tokens.sql` | Tabela `password_reset_tokens` |

**Regras:**

- **Não renomeie** migrations já aplicadas em produção (quebra `schema_migrations`).
- **Não apague** ficheiros antigos — são idempotentes ou tratados como benignos em `bin/migrate.php`.
- Novas alterações: use prefixo sequencial único a partir de **`018_`** (ex.: `018_nova_feature.sql`).

## Ordem de aplicação (SORT_STRING)

```
001 → 005
006_leads_rate_limits_soft_delete
006_must_change_password
007_reservation_code_length
007_totp_2fa
008_leads_contact
008_user_cars_quota
009_fix_demo_password_hash
009_password_reset_tokens
010 → 017
018_email_index_cleanup
019_reservation_dashboard_indexes
020_rate_limits_window_index
```

## Leads — duas linhas de evolução

Algumas bases foram criadas com o schema **legado** (006) e outras com o **moderno** (013). As migrations seguintes fazem ponte:

| Ficheiro | Papel |
|----------|--------|
| `006_leads_rate_limits_soft_delete.sql` | Cria `leads` legado se não existir |
| `008_leads_contact.sql` | Acrescenta `contact_name/email/phone` |
| `013_leads_checkin_reset.sql` | Schema moderno + check-in/out + `password_reset_tokens` |
| `017_leads_modern_schema.sql` | Copia dados legado → colunas `full_name`, `local`, `inicio`, … |

Código actual (`Lead.php`) usa **apenas** o schema moderno. `017` é obrigatória em bases que passaram por 006/008.

## Sobreposições idempotentes

- `009_password_reset_tokens.sql` e `013_leads_checkin_reset.sql` criam `password_reset_tokens` — a segunda é ignorada se a tabela já existir (código 1050).
- `006` e `013` criam `leads` com schemas diferentes — `CREATE IF NOT EXISTS` + `017` alinham instalações antigas.

## Fonte de verdade

- Instalação nova: `database/schema.sql` + `php bin/migrate.php`
- CI: schema + seed + `php bin/migrate.php`
- Estado aplicado: tabela `schema_migrations` (coluna `filename`)
