FROM php:7.4.7

# Paketler güncelleniyor.
RUN apt-get update

# Bağımlılıklar yükleniyor.
RUN apt-get update -y && apt-get install -y libmcrypt-dev git curl zlib1g-dev libzip-dev
RUN pecl install mcrypt-1.0.3
RUN docker-php-ext-install zip
RUN docker-php-ext-enable mcrypt

RUN apt-get clean

# Composer yükleniyor.
RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Laravel Envoy yükleniyor.
RUN composer global require "laravel/envoy"
