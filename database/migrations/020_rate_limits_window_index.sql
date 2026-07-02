-- Índice para purge de rate_limits em DbMaintenance::purgeExpiredRateLimits
CREATE INDEX idx_rate_limits_window ON rate_limits(window_start);
