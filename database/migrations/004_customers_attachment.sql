-- Anexo de documentos em clientes (path relativo em storage/customers/)
ALTER TABLE customers
  ADD COLUMN attachment_path VARCHAR(255) NULL AFTER zip_code;
