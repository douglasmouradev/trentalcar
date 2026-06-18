USE titanium_rental_car;

-- Contas de demonstração — altere a senha no primeiro acesso (must_change_password=1)
INSERT INTO users (name, email, password_hash, role, phone, is_active, must_change_password, lang_pref) VALUES
('Carlos Titanium', 'owner@titaniumrental.com', '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq', 'owner', '(11) 99999-0001', 1, 1, 'pt-BR'),
('Ana Operadora', 'operator@titaniumrental.com', '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq', 'operator', '(11) 98888-0002', 1, 1, 'pt-BR'),
('Cotista Demo', 'partner@titaniumrental.com', '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq', 'partner', '(11) 97777-0003', 1, 1, 'pt-BR');

INSERT INTO locations (name, address, city, state, zip_code, phone, is_active) VALUES
('Matriz Paulista', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', '(11) 3000-1000', 1),
('Filial Congonhas', 'Rua dos Funcionários, 200', 'São Paulo', 'SP', '04038-001', '(11) 3000-2000', 1),
('Guarulhos Airport', 'Rod. Hélio Smidt, s/n', 'Guarulhos', 'SP', '07190-100', '(11) 3000-3000', 1);

INSERT INTO cars (license_plate, brand, model, year, color, color_hex, category, seats, transmission, fuel, daily_rate, status, location_id, mileage, monthly_fuel, monthly_toll, monthly_wash, monthly_maintenance, monthly_extra, image_url, notes) VALUES
('ABC1D23', 'Toyota', 'Corolla', 2023, 'Prata', '#C0C0C0', 'standard', 5, 'automatic', 'flex', 189.90, 'available', 1, 12000, 450.00, 80.00, 60.00, 120.00, 0.00, NULL, NULL),
('DEF4E56', 'Jeep', 'Compass', 2024, 'Preto', '#1A1A1A', 'suv', 5, 'automatic', 'flex', 289.00, 'available', 1, 8000, 620.00, 120.00, 80.00, 200.00, 0.00, NULL, NULL),
('GHI7F89', 'Honda', 'Civic', 2022, 'Azul', '#1E3A5F', 'standard', 5, 'automatic', 'flex', 199.00, 'rented', 2, 22000, 480.00, 90.00, 60.00, 150.00, 50.00, NULL, NULL),
('JKL0G12', 'Mercedes-Benz', 'C180', 2023, 'Branco', '#F5F5F5', 'luxury', 5, 'automatic', 'gasoline', 459.00, 'available', 1, 5000, 700.00, 100.00, 120.00, 280.00, 0.00, NULL, NULL),
('MNO3H45', 'Fiat', 'Strada', 2021, 'Vermelho', '#C41E3A', 'truck', 2, 'manual', 'flex', 129.00, 'maintenance', 2, 45000, 380.00, 70.00, 40.00, 450.00, 0.00, NULL, 'Revisão programada'),
('PQR6I78', 'Volkswagen', 'T-Cross', 2024, 'Cinza', '#6B7280', 'suv', 5, 'automatic', 'flex', 249.00, 'available', 3, 3000, 520.00, 100.00, 70.00, 100.00, 0.00, NULL, NULL),
('STU9J01', 'Renault', 'Kwid', 2023, 'Laranja', '#EA580C', 'economy', 5, 'manual', 'flex', 99.90, 'inactive', 1, 15000, 280.00, 40.00, 40.00, 80.00, 0.00, NULL, NULL);

INSERT INTO user_cars (user_id, car_id, quota_percent) VALUES (3, 1, 40.00);

INSERT INTO customers (type, full_name, document, email, phone, address, city, state, zip_code, created_by, notes) VALUES
('individual', 'João da Silva', '12345678901', 'joao@email.com', '(11) 91111-1111', 'Rua A, 10', 'São Paulo', 'SP', '01000-000', 2, NULL),
('company', 'Tech Transportes LTDA', '11222333000181', 'contato@techtransportes.com', '(11) 92222-2222', 'Av. Industrial, 500', 'Guarulhos', 'SP', '07000-000', 1, NULL),
('individual', 'Maria Oliveira', '98765432100', 'maria@email.com', '(11) 93333-3333', NULL, NULL, NULL, NULL, 2, NULL);

INSERT INTO reservations (code, customer_id, car_id, operator_id, pickup_location_id, return_location_id, pickup_date, pickup_time, return_date, return_time, daily_rate, total_days, total_amount, discount, final_amount, status, payment_status, payment_method, notes) VALUES
('TRC-2026-0001', 1, 3, 2, 1, 1, CURDATE(), '10:00:00', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', 199.00, 4, 796.00, 0.00, 796.00, 'active', 'partial', 'pix', 'Cliente VIP'),
('TRC-2026-0002', 2, 1, 1, 2, 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '09:30:00', DATE_ADD(CURDATE(), INTERVAL 8 DAY), '17:30:00', 189.90, 4, 759.60, 50.00, 709.60, 'confirmed', 'paid', 'credit_card', NULL),
('TRC-2026-0003', 3, 2, 2, 1, 1, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '14:00:00', DATE_ADD(CURDATE(), INTERVAL 12 DAY), '14:00:00', 289.00, 3, 867.00, 0.00, 867.00, 'pending', 'unpaid', NULL, NULL);

INSERT INTO audit_logs (user_id, action, entity, entity_id, old_data, new_data, ip_address) VALUES
(1, 'seed', 'system', NULL, NULL, JSON_OBJECT('note', 'initial seed'), '127.0.0.1');
