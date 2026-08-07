-- Código TRC-YYYY-NNNN cabe em 16; margem para sufixos futuros
ALTER TABLE reservations MODIFY COLUMN code VARCHAR(24) NOT NULL;
