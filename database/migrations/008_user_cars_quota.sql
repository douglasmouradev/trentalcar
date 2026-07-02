-- HISTÓRICO: prefixo 008 partilhado (ver 008_leads_contact.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
ALTER TABLE user_cars
  ADD COLUMN quota_percent DECIMAL(5,2) NOT NULL DEFAULT 100.00 AFTER car_id;

UPDATE user_cars SET quota_percent = 100.00 WHERE quota_percent IS NULL OR quota_percent = 0;
