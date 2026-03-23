FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Desactivar MPM conflictivos y dejar prefork (el correcto para PHP)
RUN a2dismod mpm_event && a2enmod mpm_prefork

COPY . /var/www/html/

EXPOSE 80
