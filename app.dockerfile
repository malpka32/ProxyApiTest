FROM php:8.1

RUN apt-get update
RUN apt-get install -y libzip-dev zip
RUN docker-php-ext-install zip

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo '[xdebug]' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.mode=coverage' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN mkdir /workspace
RUN mkdir /workspace/app
WORKDIR /workspace/app

EXPOSE 80
