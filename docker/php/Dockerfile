FROM php:7.4-fpm
RUN pecl install redis-5.3.1 xdebug-2.9.6 \
    && docker-php-ext-enable redis xdebug
