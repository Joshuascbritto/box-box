#!/bin/sh
set -e

# Run migrations on every deploy. Idempotent — Laravel skips already-applied
# migrations. Use --force because we're not in an interactive TTY.
php /var/www/html/artisan migrate --force

# Cache config + routes for performance. Done at runtime so APP_KEY and
# other secrets are baked in.
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache

# Hand off to supervisord (nginx + php-fpm + scheduler).
exec /usr/bin/supervisord -c /etc/supervisord.conf
