FROM php:8.4-fpm 

ARG UID=1000
ARG GID=1000

ENV COMPOSER_HOME=/tmp/composer COMPOSER_ALLOW_SUPERUSER=0 

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/ 
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer 

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* 

RUN install-php-extensions pdo_mysql redis bcmath intl zip pcntl opcache

RUN groupmod -o -g ${GID} www-data \
    && usermod -o -u ${UID} -g ${GID} www-data \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html 

USER www-data 

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist 

COPY --chown=www-data:www-data . . 
RUN composer dump-autoload --optimize 

EXPOSE 9000 

CMD ["php-fpm"]

