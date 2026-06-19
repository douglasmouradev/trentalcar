-- Garante colunas usadas por Lead.php (schema 013) quando leads veio do schema 006.

ALTER TABLE leads ADD COLUMN full_name VARCHAR(150) NULL;
ALTER TABLE leads ADD COLUMN email VARCHAR(180) NULL;
ALTER TABLE leads ADD COLUMN phone VARCHAR(30) NULL;
ALTER TABLE leads ADD COLUMN local VARCHAR(240) NULL;
ALTER TABLE leads ADD COLUMN inicio DATE NULL;
ALTER TABLE leads ADD COLUMN fim DATE NULL;
ALTER TABLE leads ADD COLUMN mesmo_local TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE leads ADD COLUMN local_devolucao VARCHAR(240) NULL;
ALTER TABLE leads ADD COLUMN car_id INT UNSIGNED NULL;

UPDATE leads SET local = location_text WHERE local IS NULL AND location_text IS NOT NULL;
UPDATE leads SET inicio = start_date WHERE inicio IS NULL AND start_date IS NOT NULL;
UPDATE leads SET fim = end_date WHERE fim IS NULL AND end_date IS NOT NULL;
UPDATE leads SET mesmo_local = same_location WHERE same_location IS NOT NULL;
UPDATE leads SET local_devolucao = return_location_text WHERE local_devolucao IS NULL AND return_location_text IS NOT NULL;
UPDATE leads SET full_name = COALESCE(full_name, contact_name) WHERE full_name IS NULL AND contact_name IS NOT NULL;
UPDATE leads SET email = COALESCE(email, contact_email) WHERE email IS NULL AND contact_email IS NOT NULL;
UPDATE leads SET phone = COALESCE(phone, contact_phone) WHERE phone IS NULL AND contact_phone IS NOT NULL;
