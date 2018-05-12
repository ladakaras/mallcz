FROM php:7-apache

MAINTAINER Ladislav Karas

RUN apt-get update && apt-get install -y git zlib1g-dev && docker-php-ext-install pdo_mysql bcmath zip

COPY ./api /restapi/api
COPY ./config/config.ini /restapi/config
COPY ./config/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /restapi/api

EXPOSE 80

RUN composer install
RUN a2enmod rewrite

RUN apt-get clean && rm -rf /var/lib/apt/lists/*
