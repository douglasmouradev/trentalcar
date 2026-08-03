# Titanium Rental Car



SaaS de locação de veículos em **PHP 8.3+** com **MySQL 8.0+**, MVC sem framework pesado, interface em HTML/CSS/JS e i18n **pt-BR** / **en-US**.



## Requisitos



- PHP 8.3 ou superior (extensões `pdo_mysql`, `json`, `fileinfo`, `gd` recomendada para re-encodar imagens)

- MySQL 8.0+

- Apache com `mod_rewrite` **ou** servidor embutido do PHP apontando para a pasta `public`



## Instalação



1. Clone ou copie o projeto e entre na pasta `titanium-rental-car`.

2. Copie o ambiente: `cp .env.example .env` (no Windows: `copy .env.example .env`) e ajuste `APP_URL`, `DB_*` e, se necessário, `APP_BASE` (subpasta publicada).

3. Crie o banco e as tabelas:



   ```bash

   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   php bin/migrate.php
   ```



   O `schema.sql` inclui `reservations.code` como `VARCHAR(16)` e índice em `customers.email`. **Nota:** o schema cobre só o núcleo inicial (8 tabelas); tabelas como `leads`, `rate_limits` e `mail_outbox` vêm das migrations. Execute **sempre** `php bin/migrate.php` após o seed (ver `database/migrations/README.md`).



4. Garanta permissão de escrita em `public/assets/uploads` e `storage/logs`.

5. Servidor embutido (desenvolvimento):



   ```bash

   php -S localhost:8888 -t public public/router.php

   ```



   Acesse `http://localhost:8888` (ajuste conforme `APP_URL`). Use **`public/router.php`** para que rotas como `/login` e `/dashboard` funcionem (o servidor embutido não lê `.htaccess`). A landing e as páginas **LGPD** (`/privacidade`, `/termos`) estão integradas em `public/`.



6. Com Apache, defina o *DocumentRoot* para a pasta `public` ou use o `.htaccess` na raiz do projeto que encaminha para `public/index.php`.



## Contas de demonstração (seed)



| Perfil   | E-mail                    | Senha inicial |

|----------|---------------------------|---------------|

| Dono     | `owner@titaniumrental.com` | `password123` |

| Operador | `operator@titaniumrental.com` | `password123` |

| Cotista | `partner@titaniumrental.com` | `password123` |




No primeiro login, o sistema **obriga a alterar a senha** (`must_change_password`) — você será redirecionado para `/account/password` antes de entrar no painel. **Nunca** use estas credenciais em produção.

Se o login falhar após clonar o projeto, rode `php bin/migrate.php` (cria/atualiza contas demo) e `php bin/check-login.php` (diagnóstico rápido).



## Logo



A marca usa `public/assets/img/logo.png`.



## Segurança



- Senhas com `password_hash` (bcrypt, custo 12) e troca obrigatória no primeiro acesso (contas seed).

- CSRF em formulários POST, exportação CSV e cadastro rápido de cliente via API.

- Sessão com regeneração de ID e rotação de token CSRF no login; cookie `HttpOnly` + `SameSite=Lax`.

- Em produção: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true` (HTTPS).

- CSP sem `'unsafe-inline'` em scripts; configuração via `data-*` e ficheiros em `/js/`.

- Rate limiting em base de dados (login, API, leads, consulta, forgot password) — partilhado entre instâncias.

- PDO com prepared statements; escaping com `htmlspecialchars` nas views.

- Anexos de clientes servidos apenas via endpoint autenticado (`GET /customers/{id}/attachment`).

- Operadores veem apenas os próprios clientes e reservas; calendário filtrado por `operator_id`.
- Revalidação de sessão a cada pedido (utilizador desactivado é deslogado automaticamente).
- Validação server-side em reservas (cliente, veículo, local, tarifa).
- Política de senha (letras + números); troca exige senha actual.
- Rate limiting atómico; audit com IP hasheado.
- DocumentRoot **obrigatoriamente** `public/`; `.htaccess` bloqueia `.env`, `app/`, `storage/`, `bin/`.
- Uploads re-encodados (GD); pasta `uploads/` bloqueada para execução PHP.
- **TOTP encriptado** em repouso (`APP_KEY` + libsodium); re-encriptar legado: `php bin/reencrypt-totp.php`.
- **Rate limit** por IP **e** e-mail no login; limites em troca de senha e 2FA (`PasswordRateLimiter`).
- **2FA pendente** ligado a IP + User-Agent (`PendingTwoFactor`).
- **Idioma autenticado** via `POST /locale` com CSRF (visitantes usam `?lang=` só na sessão).
- **Health check** exige `HEALTH_TOKEN` em produção (`GET /health?token=…`).
- **Lead honeypot** anti-bot no formulário público.
- **ProductionGuard** no arranque: bloqueia prod mal configurada (`APP_KEY`, `HEALTH_TOKEN`, `SESSION_SECURE`, e-mails LGPD/segurança).
- **`/.well-known/security.txt`** para reporte de vulnerabilidades.
- **Contas demo** bloqueadas em produção (`ALLOW_DEMO_LOGIN=false`).
- **2FA** com códigos de recuperação de uso único.
- **Exportação e anonimização LGPD** de clientes (perfil owner).
- **E-mail multipart** (texto + HTML).
- **Monitorização:** health expandido + webhook opcional (`MONITORING_WEBHOOK_URL`).
- **Smoke tests HTTP** no CI (`HttpSmokeTest` com `SMOKE_BASE_URL`).



## Estrutura principal



- `public/index.php` — front controller.

- `public/router.php` — router para `php -S` em desenvolvimento.

- `config/routes.php` — rotas (método + caminho).

- `app/controllers`, `app/models`, `app/views`, `app/middleware`, `app/helpers`.

- `lang/pt-BR.php` e `lang/en-US.php` — traduções.

- `database/schema.sql` e `database/seed.sql` — schema e dados de exemplo.



## Idioma

Use o seletor no topo da app autenticada (`POST /locale` com CSRF). Visitantes na landing podem usar `?lang=en-US` / `?lang=pt-BR` (apenas sessão, sem gravar na BD). Utilizadores logados têm preferência persistida em `users.lang_pref`.



## Landing e leads



- O formulário na página inicial (`POST /lead`) grava pedidos na tabela **`leads`** (MySQL). Fallback JSONL em `storage/leads/` se a BD falhar.
- E-mail ao visitante + notificação à equipa (fila `mail_outbox` — `php bin/process-mail.php`).
- Páginas públicas: `/`, `/reservar`, `/consultar`.
- SEO: `GET /sitemap.xml`, `GET /robots.txt`, dados estruturados na landing.



## Auditoria e robustez

Melhorias aplicadas na revisão de código:

- **Reservas:** criação/edição com transação, `SELECT … FOR UPDATE` e bloqueio de conflito de datas; API de conflito valida `exclude_id`.
- **ACL centralizado:** `AccessControl` para reservas, clientes e voucher; operadores isolados no dashboard e calendário.
- **2FA:** rate limit TOTP; senha exigida para activar, desactivar e **iniciar** setup; segredos cifrados com `APP_KEY`.
- **Produção:** `public/index.php` recusa arranque sem `APP_KEY`, `HEALTH_TOKEN`, com `APP_DEBUG=true` ou `SESSION_SECURE=false`.
- **Router:** parâmetros `{id}` só numéricos; cotistas limitados a rotas whitelist.
- **Performance:** índices em `operator_id`, `created_by` e `audit_logs`; listagem de cotistas sem N+1.
- **Frontend:** formulário de reserva bloqueia submit com conflito; calendário com vista dia seleccionável.

### Resolução de problemas

| Sintoma | Acção |
|---------|--------|
| Login falha | `php bin/migrate.php` e `php bin/check-login.php` |
| Migration “duplicate column/key” | Normal em re-execução; `migrate.php` ignora erros benignos |
| Cotista não vê dados | Verificar `user_cars` e `quota_percent` (migration 008) |
| Health exposto | Definir `HEALTH_TOKEN` no `.env` (obrigatório em produção) |
| 2FA após activar APP_KEY | `php bin/reencrypt-totp.php` |
| Upload de imagem falha | Extensão PHP `gd` activa (incluída no Dockerfile) |

Testes unitários: `vendor/bin/phpunit` (ou `./vendor/bin/phpunit` no Linux/macOS).

Análise estática: `vendor/bin/phpstan analyse`.

Fila de e-mails: `php bin/process-mail.php` (cron recomendado a cada 5 min — use `php bin/cron-tasks.php`).

Backup MySQL: `php bin/backup.php` (automático às 03:00 via cron-tasks).

**Windows (PowerShell 5.1)** — use `;` em vez de `&&`:

```powershell
cd titanium-rental-car
php bin/migrate.php
php bin/process-mail.php
php composer.phar install
vendor\bin\phpunit
vendor\bin\phpstan analyse --memory-limit=512M
```

Se não tiver Composer global: `php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"` e depois `php composer.phar install`.

## Backup e base de dados



- Faça cópia regular do MySQL (`php bin/backup.php` ou `mysqldump`) e inclua `storage/leads/` e `storage/logs/` se usar leads e auditoria em ficheiro.

- Após atualizar o código, aplique migrações novas:



  ```bash

  php bin/migrate.php

  ```



  Ou manualmente, por exemplo:



  ```bash

  mysql -u root -p titanium_rental_car < database/migrations/006_must_change_password.sql

  mysql -u root -p titanium_rental_car < database/migrations/007_totp_2fa.sql

  ```



## Docker (opcional)



```bash

docker compose up --build

```



A app fica em `http://localhost:8888` e o MySQL em `localhost:3307`.



## Health check



`GET /health` devolve JSON com estado da base de dados e pastas graváveis (útil para monitorização).



## Segurança da conta



- **Troca obrigatória de senha** no primeiro login (`/account/password`).

- **2FA TOTP** opcional em `/account/security` (requer migration 007).

- **Busca global** na barra superior (clientes, veículos, reservas).

- **Voucher** imprimível em `/reservations/{id}/voucher`.



## Relatórios



- Na página **Relatórios** (perfil dono), use **Exportar CSV** (POST com CSRF) para descarregar o agregado mensal do intervalo de datas seleccionado.



cd "C:\Users\Douglas\Desktop\Projetos\rental car\titanium-rental-car"
php -S localhost:8888 -t public public/router.php