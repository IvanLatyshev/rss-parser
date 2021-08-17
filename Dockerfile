FROM php:8.0.3-fpm

WORKDIR '/var/www'

RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

