-- ============================================================
-- TITANIUM RENTAL CAR — Database Schema (baseline)
-- MySQL 8.0+ | UTF8MB4 | InnoDB
--
-- Este ficheiro cria o núcleo inicial (8 tabelas). Tabelas e colunas
-- adicionais (leads, rate_limits, mail_outbox, check-in, 2FA, etc.)
-- vêm de database/migrations/*.sql — execute SEMPRE após o seed:
--   php bin/migrate.php
-- Ver database/migrations/README.md para a ordem completa.
-- ============================================================

CREATE DATABASE IF NOT EXISTS titanium_rental_car
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE titanium_rental_car;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)        NOT NULL,
  email         VARCHAR(180)        NOT NULL UNIQUE,
  password_hash VARCHAR(255)        NOT NULL,
  role          ENUM('owner','operator','partner') NOT NULL DEFAULT 'operator',
  phone         VARCHAR(30),
  avatar_url    VARCHAR(255),
  is_active     TINYINT(1)          NOT NULL DEFAULT 1,
  must_change_password TINYINT(1)   NOT NULL DEFAULT 0,
  totp_secret       VARCHAR(64)     NULL DEFAULT NULL,
  lang_pref     ENUM('pt-BR','en-US') NOT NULL DEFAULT 'pt-BR',
  created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE locations (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  address    VARCHAR(255) NOT NULL,
  city       VARCHAR(80)  NOT NULL,
  state      VARCHAR(50)  NOT NULL,
  zip_code   VARCHAR(15),
  phone      VARCHAR(30),
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cars (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  license_plate    VARCHAR(15)   NOT NULL UNIQUE,
  brand            VARCHAR(60)   NOT NULL,
  model            VARCHAR(80)   NOT NULL,
  year             YEAR          NOT NULL,
  color            VARCHAR(40)   NOT NULL,
  color_hex        VARCHAR(7)    NOT NULL DEFAULT '#CCCCCC',
  category         ENUM('economy','standard','suv','luxury','van','truck') NOT NULL DEFAULT 'standard',
  seats            TINYINT       NOT NULL DEFAULT 5,
  transmission     ENUM('manual','automatic') NOT NULL DEFAULT 'automatic',
  fuel             ENUM('flex','gasoline','diesel','electric','hybrid') NOT NULL DEFAULT 'flex',
  daily_rate       DECIMAL(10,2) NOT NULL,
  status           ENUM('available','rented','maintenance','inactive') NOT NULL DEFAULT 'available',
  location_id      INT UNSIGNED,
  mileage          INT UNSIGNED  NOT NULL DEFAULT 0,
  monthly_fuel           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_toll           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_wash           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_maintenance    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_extra          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_insurance      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_document       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_ipva           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_site_rent      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_internet       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_water          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_electricity    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_phone          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_staff          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monthly_tag_annual     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  image_url        VARCHAR(255),
  notes            TEXT,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE customers (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type          ENUM('individual','company') NOT NULL DEFAULT 'individual',
  full_name     VARCHAR(150) NOT NULL,
  document      VARCHAR(20)  NOT NULL UNIQUE,
  email         VARCHAR(180),
  phone         VARCHAR(30)  NOT NULL,
  address       VARCHAR(255),
  city          VARCHAR(80),
  state         VARCHAR(50),
  zip_code      VARCHAR(15),
  notes         TEXT,
  attachment_path VARCHAR(255),
  created_by    INT UNSIGNED,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_customers_email (email)
);

CREATE TABLE reservations (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code                VARCHAR(16)   NOT NULL UNIQUE,
  customer_id         INT UNSIGNED  NOT NULL,
  car_id              INT UNSIGNED  NOT NULL,
  operator_id         INT UNSIGNED  NOT NULL,
  pickup_location_id  INT UNSIGNED  NOT NULL,
  return_location_id  INT UNSIGNED  NOT NULL,
  pickup_date         DATE          NOT NULL,
  pickup_time         TIME          NOT NULL,
  return_date         DATE          NOT NULL,
  return_time         TIME          NOT NULL,
  actual_return_at    DATETIME,
  daily_rate          DECIMAL(10,2) NOT NULL,
  total_days          SMALLINT      NOT NULL,
  total_amount        DECIMAL(10,2) NOT NULL,
  discount            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  final_amount        DECIMAL(10,2) NOT NULL,
  status              ENUM('pending','confirmed','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  payment_status      ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  payment_method      ENUM('cash','credit_card','debit_card','pix','transfer') NULL,
  notes               TEXT,
  created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id)        REFERENCES customers(id),
  FOREIGN KEY (car_id)             REFERENCES cars(id),
  FOREIGN KEY (operator_id)        REFERENCES users(id),
  FOREIGN KEY (pickup_location_id) REFERENCES locations(id),
  FOREIGN KEY (return_location_id) REFERENCES locations(id)
);

CREATE TABLE audit_logs (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED,
  action      VARCHAR(80)  NOT NULL,
  entity      VARCHAR(60)  NOT NULL,
  entity_id   INT UNSIGNED,
  old_data    JSON,
  new_data    JSON,
  ip_address  VARCHAR(45),
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_reservations_dates  ON reservations(pickup_date, return_date);
CREATE INDEX idx_reservations_car    ON reservations(car_id, status);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_operator ON reservations(operator_id);
CREATE INDEX idx_reservations_return_date ON reservations(return_date);
CREATE INDEX idx_reservations_payment_status ON reservations(payment_status);
CREATE INDEX idx_reservations_created_at ON reservations(created_at);
CREATE INDEX idx_reservations_status_pickup ON reservations(status, pickup_date);
CREATE INDEX idx_cars_status         ON cars(status);
CREATE INDEX idx_customers_created_by ON customers(created_by);
CREATE INDEX idx_audit_created ON audit_logs(created_at);

CREATE TABLE user_cars (
  user_id       INT UNSIGNED NOT NULL,
  car_id        INT UNSIGNED NOT NULL,
  quota_percent DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, car_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  INDEX idx_user_cars_car (car_id)
);

CREATE TABLE privacy_login_consent (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id          INT UNSIGNED     NOT NULL,
  ip_hash          CHAR(64)         NOT NULL,
  user_agent_hash  CHAR(64)         NOT NULL,
  created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_privacy_login_user (user_id),
  INDEX idx_privacy_login_created (created_at)
);
