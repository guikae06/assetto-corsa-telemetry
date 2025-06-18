FROM php:8-fpm-alpine

WORKDIR /var/www/html

RUN apk upgrade --update && apk add \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libpq-dev \
    postgresql-dev \
    curl \
    bash \
    git \
    tzdata \
    oniguruma-dev \
    autoconf \
    g++ \
    make

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring

COPY ./Front /var/www/html/

CMD ["php-fpm"]