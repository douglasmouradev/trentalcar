-- Índices para dashboard, relatórios e filtros operacionais
CREATE INDEX idx_reservations_return_date ON reservations(return_date);
CREATE INDEX idx_reservations_payment_status ON reservations(payment_status);
CREATE INDEX idx_reservations_created_at ON reservations(created_at);
CREATE INDEX idx_reservations_status_pickup ON reservations(status, pickup_date);
