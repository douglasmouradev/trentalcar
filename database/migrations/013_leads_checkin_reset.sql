-- HISTÓRICO: schema moderno de leads; sobrepõe 006/008 se já existirem (IF NOT EXISTS).
-- Ponte de dados legado→moderno: 017_leads_modern_schema.sql. Não renomear se já aplicada.
-- Documentação: database/migrations/README.md
CREATE TABLE IF NOT EXISTS leads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  local VARCHAR(240) NOT NULL,
  inicio DATE NOT NULL,
  fim DATE NOT NULL,
  mesmo_local TINYINT(1) NOT NULL DEFAULT 1,
  local_devolucao VARCHAR(240) NULL,
  car_id INT UNSIGNED NULL,
  status ENUM('new','contacted','converted','discarded') NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  ip_hash CHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
  INDEX idx_leads_status (status),
  INDEX idx_leads_created (created_at)
);

ALTER TABLE reservations
  ADD COLUMN actual_pickup_at DATETIME NULL AFTER actual_return_at,
  ADD COLUMN pickup_mileage INT UNSIGNED NULL AFTER actual_pickup_at,
  ADD COLUMN return_mileage INT UNSIGNED NULL AFTER pickup_mileage,
  ADD COLUMN fuel_level_pickup ENUM('empty','quarter','half','three_quarter','full') NULL AFTER return_mileage,
  ADD COLUMN fuel_level_return ENUM('empty','quarter','half','three_quarter','full') NULL AFTER fuel_level_pickup;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_reset_token (token_hash),
  INDEX idx_reset_user (user_id)
);
