-- HISTÓRICO: prefixo 006 partilhado (ver 006_leads_rate_limits_soft_delete.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
ALTER TABLE users
  ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

UPDATE users SET must_change_password = 1
WHERE email IN ('owner@titaniumrental.com', 'operator@titaniumrental.com', 'partner@titaniumrental.com');
