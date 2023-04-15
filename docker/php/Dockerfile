FROM php:8.1-fpm-alpine

RUN set -ex \
    	&& apk --no-cache add postgresql-dev nodejs yarn npm python3 g++ make\
    	&& docker-php-ext-install pdo pdo_pgsql


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN npm install -g node-sass postcss-cli autoprefixer watch --unsafe-perm

# Указываем рабочую директорию для PHP
WORKDIR /app
