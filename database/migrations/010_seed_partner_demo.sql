-- Garante conta demo do cotista (instalações antigas podem não ter o 3º utilizador do seed)
INSERT INTO users (name, email, password_hash, role, phone, is_active, must_change_password, lang_pref)
SELECT 'Cotista Demo', 'partner@titaniumrental.com', '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq', 'partner', '(11) 97777-0003', 1, 1, 'pt-BR'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'partner@titaniumrental.com');

INSERT INTO user_cars (user_id, car_id, quota_percent)
SELECT u.id, c.id, 40.00
FROM users u
INNER JOIN cars c ON c.license_plate = 'ABC1D23'
WHERE u.email = 'partner@titaniumrental.com'
  AND NOT EXISTS (
    SELECT 1 FROM user_cars uc WHERE uc.user_id = u.id AND uc.car_id = c.id
  );
