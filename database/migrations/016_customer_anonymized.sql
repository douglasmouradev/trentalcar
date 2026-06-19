-- LGPD: marca clientes anonimizados (dados pessoais removidos, reservas mantidas)

ALTER TABLE customers
  ADD COLUMN anonymized_at DATETIME NULL DEFAULT NULL AFTER updated_at;

CREATE INDEX idx_customers_anonymized ON customers(anonymized_at);
