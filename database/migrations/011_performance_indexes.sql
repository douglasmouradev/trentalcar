-- Índices para listagens, ACL de operador e busca
CREATE INDEX idx_reservations_operator ON reservations(operator_id);
CREATE INDEX idx_customers_created_by ON customers(created_by);
CREATE INDEX idx_audit_created ON audit_logs(created_at);
