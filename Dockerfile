# Base image for PHP-FPM
FROM php:8.0-fpm

# Install the necessary extensions
RUN apt-get update && apt-get install -y libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copy php.ini if you have custom settings
COPY ./backend/php.ini /usr/local/etc/php/php.ini
