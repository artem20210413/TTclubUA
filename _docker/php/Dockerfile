# Определяем аргументы
ARG PHP_VERSION
ARG APP_CODE_PATH_CONTAINER

FROM php:${PHP_VERSION}-fpm

# Устанавливаем необходимые пакеты
RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      zip unzip \
      git && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
      pdo_mysql \
      bcmath \
      gd \
      zip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Копируем файл конфигурации PHP
COPY ./_docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Устанавливаем Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Устанавливаем рабочую директорию
WORKDIR ${APP_CODE_PATH_CONTAINER}

# Определяем аргументы
#ARG PHP_VERSION
#ARG APP_CODE_PATH_CONTAINER
#
#FROM php:${PHP_VERSION}-fpm
#
#RUN apt-get update && apt-get install -y \
#      apt-utils \
#      libpq-dev \
#      libpng-dev \
#      libzip-dev \
#      zip unzip \
#      git && \
#      docker-php-ext-install pdo_mysql && \
#      docker-php-ext-install bcmath && \
##      docker-php-ext-install gd && \
#      docker-php-ext-install zip && \
#      apt-get clean && \
#      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
#
#COPY ./_docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
#
##Install composer
#ENV COMPOSER_ALLOW_SUPERUSER=1
#RUN curl -sS https://getcomposer.org/installer | php -- \
#    --filename=composer \
#    --install-dir=/usr/local/bin \
#
#WORKDIR ${APP_CODE_PATH_CONTAINER}
