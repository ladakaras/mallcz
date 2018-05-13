FROM php:7-apache

MAINTAINER Ladislav Karas

COPY . /restapi/api

WORKDIR /restapi/api

RUN mv public/index.php /var/www/html/index.php && \
    mv public/.htaccess /var/www/html/.htaccess && \
    apt-get update && \
    apt-get install -y git zlib1g-dev && \
    docker-php-ext-install pdo_mysql bcmath zip && \
    php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
    composer install && \
    composer clearcache && \
    rm /usr/bin/composer && \
    a2enmod rewrite && \
    apt-get clean && \
    apt-get remove -y git && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/*
