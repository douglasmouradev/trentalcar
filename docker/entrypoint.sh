#!/bin/bash
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-root}"
DB_DATABASE="${DB_DATABASE:-titanium_rental_car}"

echo "Aguardando MySQL em ${DB_HOST}:${DB_PORT}..."
for i in $(seq 1 60); do
  if mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; then
    break
  fi
  sleep 2
done

TABLES=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_DATABASE}'" 2>/dev/null || echo "0")

if [ "${TABLES}" = "0" ] && [ -f /var/www/html/database/schema.sql ]; then
  echo "Base vazia — aplicando schema inicial..."
  mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < /var/www/html/database/schema.sql
fi

if [ -f /var/www/html/bin/migrate.php ]; then
  echo "Executando migrations pendentes..."
  php /var/www/html/bin/migrate.php
fi

exec apache2-foreground
