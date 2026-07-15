# 1. Base Image with PHP and Apache
FROM php:8.2-apache

# 2. Install system dependencies & PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite

# 3. Configure Apache Document Root to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Install Composer cleanly
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set working directory and copy code
WORKDIR /var/www/html
COPY . .

# 6. Install Laravel dependencies for production
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 7. Set correct permissions for Laravel storage (fixes permission denied errors)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Expose port 80 (Render maps this automatically)
EXPOSE 80

CMD ["apache2-foreground"]