-- Placa do veículo opcional
USE titanium_rental_car;

ALTER TABLE cars
  MODIFY COLUMN license_plate VARCHAR(15) NULL;
