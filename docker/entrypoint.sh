#!/bin/sh
set -e
cd /var/www/html
if [ -f bin/migrate.php ]; then
  php bin/migrate.php || echo "migrate skipped"
fi
exec "$@"
