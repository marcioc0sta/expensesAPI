FROM php:8.1-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install intl opcache pdo pdo_pgsql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_mysql

WORKDIR /var/www/slim_app

# Copy the .env file into the container
COPY .env /var/www/slim_app/.env

# Copy the composer files and install dependencies
COPY composer.json composer.lock /var/www/slim_app/
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install

RUN git config --global user.email "marciocst2@gmail.com" \
    && git config --global user.name "marcioc0sta"