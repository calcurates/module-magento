FROM php:7.3-fpm-alpine

# setup php
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \

    # Sodium
    && apk add --no-cache libsodium-dev \
    && docker-php-ext-install sodium \
    && docker-php-ext-enable sodium \

    # ZIP
    && apk add --no-cache zip zlib libzip zlib-dev libzip-dev \
    && docker-php-ext-configure zip \
        --with-libzip=/usr/include/ \
    && NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
    && docker-php-ext-install -j${NPROC} zip \
    && docker-php-ext-enable zip \

    # XSLT
    && apk add --no-cache libxslt-dev libxslt \
    && docker-php-ext-install xsl \
    && docker-php-ext-enable xsl \

    # INTL
    && apk add --no-cache icu-dev icu-libs \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \

    # SOAP
    && apk add --no-cache libxml2-dev \
    && docker-php-ext-install soap \
    && docker-php-ext-enable soap \

    # GD
    && apk add --no-cache freetype libpng libwebp libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd \
        --with-gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-webp-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
    && docker-php-ext-install -j${NPROC} gd \
    && docker-php-ext-enable gd \

    # Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \

    # Bcmath
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \

    # Mysql
    && apk add --no-cache mysql-client \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \


    # Cleanup
    && apk del --no-cache .build-deps \
    && apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && apk del --no-cache icu-dev \
    && apk del --no-cache libxml2-dev \
    && apk del --no-cache libxslt-dev \
    && apk del --no-cache zlib-dev libzip-dev \
    && apk del --no-cache libsodium-dev \
    && rm -rf /tmp/* /var/cache/apk/* \

    # Default php config
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


# custom configs
COPY opcache.ini $PHP_INI_DIR/conf.d/
COPY php.ini $PHP_INI_DIR/
COPY auth.json /.composer/


# utilites
RUN set -xe \
    && apk --no-cache add unzip gzip tar htop


# Composer
RUN curl -L -o /composer.phar https://github.com/composer/composer/releases/download/1.10.5/composer.phar \
    && chmod 777 /composer.phar \
ENV COMPOSER_HOME=/.composer


# template of magento2
RUN set -xe \
    && mkdir /templates \
    && cd /templates \
    && /composer.phar create-project --repository=https://repo.magento.com/ magento/project-community-edition:^2.3.4 mg234 \
    && cd mg234 \
    && tar -zcvf ../mg234.tar.gz * \
    && cd .. \
    && rm -rf mg234 \
    && /composer.phar clear-cache


WORKDIR /var/www/magento2

COPY entrypoint.sh /
RUN chmod 777 /entrypoint.sh

# avoid stupid docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
