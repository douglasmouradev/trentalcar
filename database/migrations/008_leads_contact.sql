-- HISTÓRICO: prefixo 008 partilhado (ver 008_user_cars_quota.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
-- Idempotente: colunas legado do schema 006. Sem AFTER (compatível com schema 013).
ALTER TABLE leads ADD COLUMN contact_name VARCHAR(120) NULL;
ALTER TABLE leads ADD COLUMN contact_email VARCHAR(180) NULL;
ALTER TABLE leads ADD COLUMN contact_phone VARCHAR(30) NULL;
