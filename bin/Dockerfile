FROM php:7-apache

MAINTAINER Ladislav Karas

COPY . /restapi/bin

WORKDIR /restapi/bin

RUN apt-get update && \
    apt-get install -y git zlib1g-dev && \
    docker-php-ext-install bcmath zip && \
    php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
    composer install && \
    composer clearcache && \
    rm /usr/bin/composer && \
    apt-get clean && \
    apt-get remove -y git && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/*
