#!/bin/bash

# Fix permissions for Laravel storage and cache directories
echo "Setting proper permissions for Laravel..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Run database migrations (skip if tables exist)
echo "Running database migrations..."
php artisan migrate --force || echo "Migrations skipped - tables may already exist"

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Clear and cache config, routes, and views
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
echo "Starting Apache server..."
exec apache2-foreground