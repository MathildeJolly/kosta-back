
FROM php:7.4-fpm

RUN apt-get update -y
RUN apt purge php-pear php-zip
RUN apt-get install -y openssl zip unzip git nginx libwebp-dev \
libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev nano htop && pecl install xdebug
RUN apt-get install -y libzip-dev zip
RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    curl \
    libssl-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    libzip-dev \
    -y mariadb-client

RUN docker-php-ext-install exif
RUN docker-php-ext-enable exif

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip


RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) gd

RUN apt update && apt install -y libc-client-dev libkrb5-dev && rm -r /var/lib/apt/lists/*


RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure opcache --enable-opcache \
&& docker-php-ext-install opcache
RUN docker-php-ext-install pdo pdo_mysql gd bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir /var/kosta

WORKDIR /var/kosta

COPY . /var/kosta

RUN cp .env.docker .env
RUN chown -R :www-data /var/kosta
RUN composer install
RUN mkdir /var/logs

RUN chmod -R 775 /var/kosta/storage/

COPY ./nginx-dev.conf /etc/nginx/conf.d/


RUN rm -rf /etc/nginx/sites-enabled/

EXPOSE 80

CMD ["php-fpm", "-D;", "nginx", "-g", "daemon off;"]
