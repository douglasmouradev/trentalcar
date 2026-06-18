-- Reaplica hash demo para cotista (contas criadas após 009 podem ficar com hash antigo)
UPDATE users SET password_hash = '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq'
WHERE email = 'partner@titaniumrental.com';
