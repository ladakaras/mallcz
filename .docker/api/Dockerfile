FROM php:7-apache

MAINTAINER Ladislav Karas

RUN apt-get update && apt-get install -y git zlib1g-dev && docker-php-ext-install pdo_mysql bcmath zip

COPY ./api/include /restapi/api/include
COPY ./api/composer.json /restapi/api
COPY ./api/composer.lock /restapi/api
COPY ./config /restapi/config
COPY ./api/public /var/www/html

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /restapi/api

EXPOSE 80

RUN composer install

RUN apt-get clean && rm -rf /var/lib/apt/lists/*
