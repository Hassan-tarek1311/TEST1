# Base image: PHP 8.4 with Apache (matches composer.lock / Symfony 8)
FROM php:8.4-apache

# ===========================
# 1. Install system dependencies
# ===========================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ===========================
# 2. Install PHP extensions
# ===========================
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# ===========================
# 3. Install Composer
# ===========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ===========================
# 4. Enable Apache mod_rewrite (مهم جداً لـ Laravel)
# ===========================
RUN a2enmod rewrite

# ===========================
# 5. Set Apache document root to Laravel's public folder
# ===========================
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# ===========================
# 6. Set working directory
# ===========================
WORKDIR /var/www/html

# ===========================
# 7. Copy project files
# ===========================
COPY ./app /var/www/html

# ===========================
# 8. Install Laravel dependencies
# ===========================
RUN composer install --no-interaction --optimize-autoloader

# ===========================
# 9. Set permissions
# ===========================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ===========================
# 10. Copy and set entrypoint
# ===========================
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
