ARG DOCKER_REGISTRY=docker.io
ARG PHP_VERSION=7.4.19
ARG GITHUB_TOKEN=c2e36fd1116557079fab447dec153664e7c0c3b9
ARG APCU_VERSION=5.1.18

FROM ${DOCKER_REGISTRY}/bitnami/php-fpm:${PHP_VERSION} AS php_dev

ENV PHP_INI_DIR="/opt/bitnami/php/etc"
COPY docker-compose/php/*.ini $PHP_INI_DIR/conf.d/
COPY docker-compose/php/php-fpm.conf $PHP_INI_DIR/php-fpm.conf

ARG APCU_VERSION
ARG GITHUB_TOKEN

RUN install_packages autoconf unzip libpq-dev g++ make git libjpeg-dev libpng-dev libtiff-dev libgif-dev && \
    yes 'yes' | pecl install -f apcu-${APCU_VERSION} && \
    rm -rf "$PHP_INI_DIR/conf.d/php_custom.ini" && \
    rm -rf "$PHP_INI_DIR/conf.d/php_opcache.ini" && \
    echo "extension=pgsql.so" >> $PHP_INI_DIR/conf.d/pgsql.ini && \
    echo "extension=pdo_pgsql.so" >> $PHP_INI_DIR/conf.d/pgsql.ini && \
    pecl install pcov && \
    echo "extension=pcov.so" >> $PHP_INI_DIR/conf.d/pcov.ini && \
    mkdir -m 777 -p /.composer && \
    composer self-update --2 && \
    composer config --global --auth github-oauth.github.com $GITHUB_TOKEN && \
    curl -sSL https://storage.googleapis.com/downloads.webmproject.org/releases/webp/libwebp-1.2.0.tar.gz > /opt/libwebp.tar.gz && \
    cd /opt && \
    tar xvf /opt/libwebp.tar.gz && \
    cd libwebp-1.2.0 && \
    ./configure && \
    make

FROM php_dev as install_vendors

WORKDIR /app

# prevent the reinstallation of vendors at every changes in the source code
ADD composer.json composer.lock symfony.lock ./
RUN set -eux && \
    composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-ansi && \
    composer clear-cache --no-ansi

ADD . /app

FROM ${DOCKER_REGISTRY}/bitnami/php-fpm:${PHP_VERSION}-prod AS php_prod

ENV PHP_INI_DIR="/opt/bitnami/php/etc" APP_ENV=prod APP_DEBUG=0 SERVER_ENV=prod

# COPY FILES / DYNAMIC PART
COPY --from=install_vendors /app /app
COPY --from=php_dev /opt/libwebp-1.2.0/src/.libs/libwebp.so* /usr/lib/x86_64-linux-gnu/
WORKDIR /app

RUN set -eux \
  && apt-get remove -y libwebp6 \
  && ln -sv /usr/lib/x86_64-linux-gnu/libwebp.so.7 /usr/lib/x86_64-linux-gnu/libwebp.so.6 \
  && install_packages libpq5 libfcgi0ldbl libfcgi-bin \
  && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
  && cp docker-compose/php/*.ini $PHP_INI_DIR/conf.d/ \
  && rm -f $PHP_INI_DIR/php-fpm.d/* \
  && cp docker-compose/php/php-fpm.conf $PHP_INI_DIR/php-fpm.conf \
  && cp docker-compose/php/www.conf $PHP_INI_DIR/php-fpm.d/ \
  && echo "extension=pgsql.so" >> $PHP_INI_DIR/conf.d/pgsql.ini \
  && echo "extension=pdo_pgsql.so" >> $PHP_INI_DIR/conf.d/pgsql.ini \
  && echo "opcache.preload = /app/config/preload.php" >> $PHP_INI_DIR/conf.d/php_opcache.ini \
  && echo "opcache.preload_user = www-data" >> $PHP_INI_DIR/conf.d/php_opcache.ini \
  && mkdir -p var/cache var/log \
  && composer self-update --2 \
  && composer global require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative \
  && composer clear-cache --no-ansi \
  && composer dump-autoload --optimize --classmap-authoritative --no-dev \
  && composer dump-env prod \
  && composer run-script --no-dev --no-ansi post-install-cmd \
  && chmod +x bin/console \
  && bin/console cache:clear --no-warmup && bin/console cache:warmup \
  && composer clear-cache --no-ansi \
  && chown -R www-data:www-data /app/var \
  && sync

USER www-data
CMD ["php-fpm", "-F", "--pid", "/tmp/php-fpm.pid", "-y", "/opt/bitnami/php/etc/php-fpm.conf"]
