-- Completa gastos fixos com as mesmas categorias do custo mensal por veículo
USE titanium_rental_car;

ALTER TABLE fixed_costs ADD COLUMN insurance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER id;
ALTER TABLE fixed_costs ADD COLUMN document DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER insurance;
ALTER TABLE fixed_costs ADD COLUMN plate DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER document;
ALTER TABLE fixed_costs ADD COLUMN wash DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER plate;
ALTER TABLE fixed_costs ADD COLUMN tag_annual DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER staff;
ALTER TABLE fixed_costs ADD COLUMN fuel DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tag_annual;
ALTER TABLE fixed_costs ADD COLUMN toll DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER fuel;
ALTER TABLE fixed_costs ADD COLUMN maintenance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER toll;
