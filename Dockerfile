FROM php:7-apache

MAINTAINER Ladislav Karas

COPY ./api /restapi/api
COPY ./api/public /var/www/html
COPY ./config /restapi/config

WORKDIR /restapi/api

RUN apt-get update \
  && apt-get install -y git zlib1g-dev nano \
  && docker-php-ext-install pdo_mysql bcmath zip \
  && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
  && composer install \
  && composer clearcache \
  && rm /usr/bin/composer \
  && a2enmod rewrite \
  && apt-get clean \
  && apt-get remove -y git \
  && apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/*

EXPOSE 80

