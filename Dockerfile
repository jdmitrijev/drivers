# Use official PHP 8.4 FPM image
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    apt-utils \
    zlib1g-dev \
    sendmail \
    libpng-dev \
    libonig-dev \
    libicu-dev \
    nano \
    libxml2-dev \
    libzip-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxpm-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl mbstring soap \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_pgsql pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy existing application directory contents
COPY ./src /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy custom PHP settings
COPY docker-config/php/local.ini /usr/local/etc/php/conf.d/