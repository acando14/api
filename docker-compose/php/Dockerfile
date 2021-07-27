ARG PHP_VERSION=7.4.15
ARG APP_NAME=symfony
ARG APCU_VERSION=5.1.18

FROM php:${PHP_VERSION}-fpm AS php_base

ARG APCU_VERSION

RUN apt-get update && apt-get -y install zlib1g-dev unzip libpq-dev git-core libmcrypt-dev vim libfcgi0ldbl \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libonig-dev libicu-dev && \
    yes 'yes' | pecl install -f apcu-${APCU_VERSION} && docker-php-ext-enable apcu && \
    docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo mysqli pdo_mysql mbstring opcache -j$(nproc) gd intl && \
    echo "memory_limit = -1" > /usr/local/etc/php/conf.d/memory_limit.ini && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    docker-php-ext-enable pdo_mysql

FROM php_base AS php_dev

COPY docker-compose/php/*.ini $PHP_INI_DIR/conf.d/
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    rm -rf "$PHP_INI_DIR/conf.d/php_custom.ini" && \
    rm -rf "$PHP_INI_DIR/conf.d/php_opcache.ini" && \
    mkdir -m 777 -p /.composer && \
    pecl install pcov && docker-php-ext-enable pcov

FROM php_base as install_vendors

ARG APP_NAME

RUN composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache

WORKDIR /var/www/html/$APP_NAME

# prevent the reinstallation of vendors at every changes in the source code
ADD composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest --no-ansi; \
    composer clear-cache --no-ansi

ADD . /var/www/html/$APP_NAME

FROM php_base AS php_prod

ARG APP_NAME
ENV APP_ENV=prod APP_DEBUG=0 SERVER_ENV=prod

# COPY FILES / DYNAMIC PART
COPY --from=install_vendors /var/www/html/$APP_NAME/ /var/www/html/$APP_NAME
WORKDIR /var/www/html/$APP_NAME/

RUN set -eux; \
  mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
  && cp -R docker-compose/php/*.ini $PHP_INI_DIR/conf.d/ \
  && echo "opcache.preload = /var/www/html/$APP_NAME/config/preload.php" >> $PHP_INI_DIR/conf.d/php_opcache.ini \
  && echo "opcache.preload_user = root" >> $PHP_INI_DIR/conf.d/php_opcache.ini \
  && mv docker-compose/php/openssl.cnf /etc/ssl/openssl.cnf \
  && composer dump-autoload --optimize --classmap-authoritative --no-dev \
  && composer run-script --no-dev --no-ansi post-install-cmd \
  && chmod +x bin/console \
  && bin/console cache:clear --no-warmup && bin/console cache:warmup \
  && sync
