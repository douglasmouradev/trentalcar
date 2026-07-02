-- HISTÓRICO: prefixo 009 partilhado (ver 009_password_reset_tokens.sql).
-- Não renomear se já aplicada. Documentação: database/migrations/README.md
-- Corrige hash das contas demo (password123) — o hash anterior no seed estava inválido
UPDATE users SET password_hash = '$2y$12$xpF/1qt9QPGLlmWE0DwUCu9KyuiK1UdhMojVR3fngzCbSaJ3hcfdq'
WHERE email IN (
  'owner@titaniumrental.com',
  'operator@titaniumrental.com',
  'partner@titaniumrental.com'
);
