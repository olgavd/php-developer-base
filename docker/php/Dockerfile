FROM php:8.4-apache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && chown -R root:root /var/www/html \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer  \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && apt-get update  \
    && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
    && docker-php-ext-install pdo_mysql mysqli



