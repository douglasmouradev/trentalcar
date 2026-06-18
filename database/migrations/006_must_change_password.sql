ALTER TABLE users
  ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

UPDATE users SET must_change_password = 1
WHERE email IN ('owner@titaniumrental.com', 'operator@titaniumrental.com', 'partner@titaniumrental.com');
