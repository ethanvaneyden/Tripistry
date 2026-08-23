FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY client/ .
COPY server/ ./server/
COPY tripistry-pathinfo.conf /etc/apache2/conf-available/tripistry-pathinfo.conf

EXPOSE 80