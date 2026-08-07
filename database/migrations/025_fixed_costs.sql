-- Gastos fixos mensais da operação (não por veículo)
USE titanium_rental_car;

CREATE TABLE IF NOT EXISTS fixed_costs (
  id            TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  site_rent     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  internet      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  water         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  electricity   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  phone         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  staff         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  extra         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO fixed_costs (id, site_rent, internet, water, electricity, phone, staff, extra)
SELECT 1, 0, 0, 0, 0, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM fixed_costs WHERE id = 1 LIMIT 1);
