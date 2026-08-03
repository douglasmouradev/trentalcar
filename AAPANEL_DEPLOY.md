# Deploy no aaPanel via Docker

## Veredito

O projeto e compativel com deploy no aaPanel usando Docker/Compose. Nao use o `docker-compose.yml` atual em producao: ele e voltado para desenvolvimento local (`APP_ENV=local`, `APP_DEBUG=true`, bind mount do projeto e porta do MySQL exposta).

Para producao, use um Compose separado com:

- `app`: PHP 8.3 + Apache, expondo somente a porta HTTP para o proxy do aaPanel.
- `worker`: executa `php bin/cron-tasks.php` a cada 5 minutos.
- `db`: MySQL 8.0 em rede interna, sem porta publica.
- volumes persistentes para MySQL, `storage/` e `public/assets/uploads/`.
- arquivo `.env` real montado dentro do container como `/var/www/html/.env`.

## Pontos de arquitetura validados

- O DocumentRoot correto e `public/`.
- O Dockerfile usa Apache com `FallbackResource /index.php`, entao rotas como `/login`, `/dashboard` e `/health` funcionam sem expor `app/`, `storage/`, `config/` ou `bin/`.
- O app tem `ProductionGuard`: em ambiente publicado, ele bloqueia boot se faltarem `APP_KEY`, `HEALTH_TOKEN`, credenciais do DB, emails LGPD/seguranca ou se `APP_DEBUG=true`.
- `bin/migrate.php` aplica apenas migrations. Em uma base nova, `database/schema.sql` precisa ser aplicado primeiro.
- `bin/cron-tasks.php` processa emails, rotaciona logs, limpa dados expirados e roda backup diario as 03:00.

## 1. Preparar servidor

1. No aaPanel, instale/ative Docker.
2. Use Nginx no aaPanel para o proxy do dominio.
3. Aponte o DNS do dominio para o IP do servidor.
4. Suba o codigo para, por exemplo:

```bash
/www/wwwroot/titanium-rental-car
```

Nao coloque o `.env` real dentro da pasta do projeto, para ele nao entrar no build da imagem. Use um arquivo fora do build context, por exemplo:

```bash
/www/wwwroot/titanium-rental-car.env
```

## 2. Criar o `.env` de producao

Crie `/www/wwwroot/titanium-rental-car.env` com valores reais:

```dotenv
APP_NAME="Titanium Rental Car"
APP_URL=https://seudominio.com.br
APP_BASE=
APP_ENV=production
APP_DEBUG=false
APP_LANDING=true
APP_DEFAULT_LANG=pt-BR
APP_PER_PAGE=15

APP_KEY=COLE_64_CHARS_HEX_AQUI
HEALTH_TOKEN=COLE_TOKEN_LONGO_AQUI
SESSION_SECURE=true
SESSION_LIFETIME=480

DB_HOST=db
DB_PORT=3306
DB_DATABASE=titanium_rental_car
DB_USERNAME=titanium
DB_PASSWORD=TROQUE_SENHA_DB_APP

ALLOW_DEMO_LOGIN=false

PRIVACY_CONTROLLER_NAME="Sua Empresa"
PRIVACY_CONTROLLER_EIN="61-2244130"
PRIVACY_ADDRESS="Endereco completo"
PRIVACY_DPO_EMAIL=privacidade@seudominio.com.br
PRIVACY_DPO_PHONE=
SECURITY_CONTACT_EMAIL=seguranca@seudominio.com.br

CONTACT_WHATSAPP=5500000000000
CONTACT_PHONE="(00) 0000-0000"
CONTACT_EMAIL=contato@titaniumrentalcar.com
CONTACT_ADDRESS="Endereco publico"
BUSINESS_LEGAL_NAME="Sua Empresa Ltda"
BUSINESS_EIN="61-2244130"
# Textos de horário/resposta/diária vêm de lang/ (contact.*). Opcionais:
# BUSINESS_HOURS="Domingo a domingo, 24 horas"
# BUSINESS_HOURS_EN="Sunday to Sunday, 24 hours"
# BUSINESS_RESPONSE_TIME="2 horas uteis"
# BUSINESS_RESPONSE_TIME_EN="2 business hours"
# BUSINESS_MIN_RATE="R$ 99,90"
# BUSINESS_MIN_RATE_EN="$99.90"

MAIL_FROM=noreply@seudominio.com.br
MAIL_FROM_NAME="Titanium Rental Car"
MAIL_SMTP_HOST=smtp.seudominio.com.br
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=noreply@seudominio.com.br
MAIL_SMTP_PASS=TROQUE_SENHA_SMTP
MAIL_SMTP_SECURE=tls
MAIL_NOTIFY=reservas@seudominio.com.br

MAX_UPLOAD_SIZE=5242880
UPLOAD_PATH=public/assets/uploads
MONITORING_WEBHOOK_URL=
```

Gere `APP_KEY` e `HEALTH_TOKEN` com tokens fortes. Exemplo:

```bash
openssl rand -hex 32
openssl rand -hex 32
```

## 3. Compose para colar no aaPanel

Se for executar pelo terminal, prefira o arquivo versionado `docker-compose.prod.yml` que fica na raiz do projeto. Ele usa caminhos relativos ao diretorio atual e espera que o `.env` esteja no mesmo diretorio do projeto.

No aaPanel, acesse `Docker` > `Compose` > `Add Compose`. Use o conteudo abaixo como `compose.yaml` e deixe o campo `.env Content` vazio, pois o app le o arquivo montado em `/var/www/html/.env`.

Troque `TROQUE_ROOT_DB` e `TROQUE_SENHA_DB_APP`. O valor de `MYSQL_PASSWORD` deve ser igual ao `DB_PASSWORD` do arquivo `.env`. Se o projeto ficar em outro diretorio, ajuste os caminhos absolutos `/www/wwwroot/titanium-rental-car` e `/www/wwwroot/titanium-rental-car.env`.

```yaml
services:
  app:
    build:
      context: /www/wwwroot/titanium-rental-car
    container_name: titanium_app
    restart: unless-stopped
    ports:
      - "127.0.0.1:8888:80"
    volumes:
      - /www/wwwroot/titanium-rental-car.env:/var/www/html/.env:ro
      - titanium_storage:/var/www/html/storage
      - titanium_uploads:/var/www/html/public/assets/uploads
    depends_on:
      db:
        condition: service_healthy

  worker:
    build:
      context: /www/wwwroot/titanium-rental-car
    container_name: titanium_worker
    restart: unless-stopped
    entrypoint: ["/bin/sh", "-c"]
    command: "cd /var/www/html && while true; do php bin/cron-tasks.php; sleep 300; done"
    volumes:
      - /www/wwwroot/titanium-rental-car.env:/var/www/html/.env:ro
      - titanium_storage:/var/www/html/storage
      - titanium_uploads:/var/www/html/public/assets/uploads
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mysql:8.0
    container_name: titanium_db
    restart: unless-stopped
    command:
      - "--character-set-server=utf8mb4"
      - "--collation-server=utf8mb4_unicode_ci"
    environment:
      MYSQL_ROOT_PASSWORD: "TROQUE_ROOT_DB"
      MYSQL_DATABASE: titanium_rental_car
      MYSQL_USER: titanium
      MYSQL_PASSWORD: "TROQUE_SENHA_DB_APP"
    volumes:
      - titanium_db_data:/var/lib/mysql
      - /www/wwwroot/titanium-rental-car/database/schema.sql:/docker-entrypoint-initdb.d/001-schema.sql:ro
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h localhost -uroot -p$${MYSQL_ROOT_PASSWORD} --silent"]
      interval: 10s
      timeout: 5s
      retries: 10

volumes:
  titanium_db_data:
  titanium_storage:
  titanium_uploads:
```

## 4. Primeira inicializacao

1. Inicie o Compose no aaPanel.
2. Abra os logs de `titanium_app` e confirme que nao aparece `Configuração inválida`.
3. Abra o terminal do container `titanium_app`.
4. Rode:

```bash
cd /var/www/html
php bin/migrate.php
```

5. Crie um owner real e desative contas demo. Troque email, nome e senha temporaria:

```bash
php -r 'require "bin/bootstrap-cli.php";
Database::prepare("UPDATE users SET is_active = 0 WHERE email IN (?,?,?)")->execute(["owner@titaniumrental.com","operator@titaniumrental.com","partner@titaniumrental.com"]);
if (!User::findByEmail("admin@seudominio.com.br")) {
    User::create([
        "name" => "Administrador",
        "email" => "admin@seudominio.com.br",
        "password" => "SenhaTemporaria123",
        "role" => "owner",
        "phone" => "",
        "is_active" => 1,
        "must_change_password" => 1,
        "lang_pref" => "pt-BR",
    ]);
}
echo "owner ok\n";'
```

6. Teste o health check no terminal do servidor ou pelo navegador:

```bash
curl -f "http://127.0.0.1:8888/health?token=SEU_HEALTH_TOKEN"
```

## 5. Publicar dominio no aaPanel

1. Va em `Website` > `Proxy Project` ou use a acao `Proxy` do container no menu Docker.
2. Crie o dominio `seudominio.com.br`.
3. Configure proxy para:

```text
http://127.0.0.1:8888
```

4. Em SSL, emita Let's Encrypt ou instale certificado comercial.
5. Ative redirecionamento HTTPS.
6. Confirme que `APP_URL` no `.env` e exatamente a URL HTTPS publicada.

## 6. Pos-deploy

1. Acesse `https://seudominio.com.br/login`.
2. Entre com o owner real e altere a senha no primeiro acesso.
3. Ative 2FA para o owner.
4. Cadastre locais, carros, operadores e dados reais da empresa.
5. Teste lead publico, reserva, consulta, upload de imagem e envio de email.
6. Rode backup manual:

```bash
cd /var/www/html
php bin/backup.php
php bin/verify-backup.php
```

7. Configure monitoramento externo para:

```text
https://seudominio.com.br/health?token=SEU_HEALTH_TOKEN
```

## 7. Atualizacoes futuras

1. Faca backup do banco e dos volumes.
2. Atualize o codigo no servidor.
3. No aaPanel, recrie/rebuild a imagem do Compose.
4. Rode:

```bash
cd /var/www/html
php bin/migrate.php
```

5. Valide `/health`, login e um fluxo critico de reserva.
