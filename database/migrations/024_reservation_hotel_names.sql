-- Nome do hotel na retirada/devolução (quando local = Entrega no hotel)
USE titanium_rental_car;

ALTER TABLE reservations
  ADD COLUMN pickup_hotel_name VARCHAR(120) NULL AFTER pickup_location_id,
  ADD COLUMN return_hotel_name VARCHAR(120) NULL AFTER return_location_id;
