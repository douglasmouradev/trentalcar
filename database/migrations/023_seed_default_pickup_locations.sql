-- Locais padrão alinhados ao site (MIA / MCO / hotel)
USE titanium_rental_car;

INSERT INTO locations (name, address, city, state, zip_code, phone, is_active)
SELECT 'Aeroporto MIA', 'Miami International Airport', 'Miami', 'FL', '33126', NULL, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE name = 'Aeroporto MIA' LIMIT 1);

INSERT INTO locations (name, address, city, state, zip_code, phone, is_active)
SELECT 'Aeroporto MCO', 'Orlando International Airport', 'Orlando', 'FL', '32827', NULL, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE name = 'Aeroporto MCO' LIMIT 1);

INSERT INTO locations (name, address, city, state, zip_code, phone, is_active)
SELECT 'Entrega no hotel', 'Entrega sob demanda (hotel)', 'Orlando', 'FL', NULL, NULL, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE name = 'Entrega no hotel' LIMIT 1);

UPDATE locations SET is_active = 1
WHERE name IN ('Aeroporto MIA', 'Aeroporto MCO', 'Entrega no hotel') AND is_active = 0;
