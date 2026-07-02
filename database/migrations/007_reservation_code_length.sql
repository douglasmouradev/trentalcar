-- HISTÓRICO: prefixo 007 partilhado (ver 007_totp_2fa.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
ALTER TABLE reservations MODIFY COLUMN code VARCHAR(16) NOT NULL;
