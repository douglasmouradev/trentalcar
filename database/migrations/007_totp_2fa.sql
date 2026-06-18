ALTER TABLE users
  ADD COLUMN totp_secret VARCHAR(64) NULL DEFAULT NULL AFTER must_change_password;
