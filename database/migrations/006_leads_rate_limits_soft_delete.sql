-- HISTÓRICO: prefixo 006 partilhado (ver 006_must_change_password.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
-- Leads, rate limits distribuídos, soft delete de veículos, índices

CREATE TABLE IF NOT EXISTS leads (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_text         VARCHAR(240) NOT NULL,
  start_date            DATE         NOT NULL,
  end_date              DATE         NOT NULL,
  same_location         TINYINT(1)   NOT NULL DEFAULT 1,
  return_location_text  VARCHAR(240),
  ip_hash               CHAR(64)     NOT NULL,
  status                ENUM('new','contacted','converted','archived') NOT NULL DEFAULT 'new',
  notes                 TEXT,
  created_at            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_leads_created (created_at),
  INDEX idx_leads_status (status)
);

CREATE TABLE IF NOT EXISTS rate_limits (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bucket_key   VARCHAR(128) NOT NULL,
  hits         INT UNSIGNED NOT NULL DEFAULT 0,
  window_start INT UNSIGNED NOT NULL,
  UNIQUE KEY uk_rate_limits_bucket (bucket_key)
);

ALTER TABLE cars
  ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at;

CREATE INDEX idx_cars_deleted ON cars(deleted_at);
CREATE INDEX idx_customers_document ON customers(document);
