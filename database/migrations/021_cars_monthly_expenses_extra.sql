-- Gastos mensais adicionais por veículo
USE titanium_rental_car;

ALTER TABLE cars
  ADD COLUMN monthly_insurance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_extra,
  ADD COLUMN monthly_document DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_insurance,
  ADD COLUMN monthly_ipva DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_document,
  ADD COLUMN monthly_site_rent DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_ipva,
  ADD COLUMN monthly_internet DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_site_rent,
  ADD COLUMN monthly_water DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_internet,
  ADD COLUMN monthly_electricity DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_water,
  ADD COLUMN monthly_phone DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_electricity,
  ADD COLUMN monthly_staff DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_phone,
  ADD COLUMN monthly_tag_annual DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER monthly_staff;
