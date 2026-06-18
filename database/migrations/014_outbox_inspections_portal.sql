-- Fila de e-mails, vistorias com foto e taxas extras

CREATE TABLE IF NOT EXISTS mail_outbox (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  to_email VARCHAR(180) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  last_error VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sent_at TIMESTAMP NULL,
  INDEX idx_mail_outbox_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservation_inspections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT UNSIGNED NOT NULL,
  kind ENUM('pickup','return') NOT NULL,
  mileage INT UNSIGNED NULL,
  fuel_level VARCHAR(20) NULL,
  damage_notes TEXT NULL,
  extra_charges DECIMAL(10,2) NOT NULL DEFAULT 0,
  photo_path VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_inspection_reservation (reservation_id, kind),
  CONSTRAINT fk_inspection_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE reservations
  ADD COLUMN extra_charges DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER final_amount;
