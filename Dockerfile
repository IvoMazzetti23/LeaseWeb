FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install mbstring zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install --optimize-autoloader --prefer-dist

COPY . .

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
