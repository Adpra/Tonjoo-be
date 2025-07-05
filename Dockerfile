# Dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose port 8000
EXPOSE 8000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000
