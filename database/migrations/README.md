# Migrations

Aplique sempre com `php bin/migrate.php` após actualizar o código.

## Numeração

Existem ficheiros com prefixos duplicados (`006_*`, `007_*`, …) de evoluções paralelas. O script ordena por **nome de ficheiro** (`SORT_STRING`) e regista o que já correu em `schema_migrations`. **Não renomeie** migrations já aplicadas em produção.

Para novas alterações use prefixos sequenciais únicos a partir de `015_` (ex.: `015_nova_feature.sql`).

## Fonte de verdade

- Instalação nova: `database/schema.sql` + `php bin/migrate.php`
- CI: schema + seed + migrations pendentes
