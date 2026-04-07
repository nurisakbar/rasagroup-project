#!/bin/sh

# Set PHP-FPM permissions
# chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Run Laravel tasks
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migration (optional, usually done in CI/CD or manually first time)
# php artisan migrate --force

exec "$@"
