-- HISTÓRICO: prefixo 007 partilhado (ver 007_reservation_code_length.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
ALTER TABLE users
  ADD COLUMN totp_secret VARCHAR(64) NULL DEFAULT NULL AFTER must_change_password;
