FROM php:7-fpm

RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pgsql pdo_pgsql && \
    docker-php-ext-enable pdo pgsql pdo_pgsql
