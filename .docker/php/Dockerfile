FROM php:7.4-fpm-alpine3.13

# Update repo and install some utilites
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk --no-cache add htop unzip gzip tar curl

# Composer
RUN set -xe \
    && curl -L -o /composer.phar https://github.com/composer/composer/releases/download/2.0.11/composer.phar \
    && chmod 755 /composer.phar
ENV COMPOSER_HOME=/.composer
COPY auth.json /.composer/

# PHP
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \

    # Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \

    # Sodium
    && apk add --no-cache libsodium-dev \
    && docker-php-ext-install sodium \
    && docker-php-ext-enable sodium \

    # ZIP
    && apk add --no-cache libzip libzip-dev \
    && docker-php-ext-install zip \
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

    # Bcmath
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \

    # Mysql
    && apk add --no-cache mysql-client \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \

    # Sockets
    && docker-php-ext-install sockets \
    && docker-php-ext-enable sockets \

    # GD
    && apk add --no-cache freetype libpng libwebp libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd \
        --enable-gd \
        --with-freetype \
        --with-webp \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

# Cleanup
RUN set -xe \
    && apk del --no-cache .build-deps \
    && apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && apk del --no-cache icu-dev \
    && apk del --no-cache libxml2-dev \
    && apk del --no-cache libxslt-dev \
    && apk del --no-cache libzip-dev \
    && apk del --no-cache libsodium-dev \
    && rm -rf /tmp/* /var/cache/apk/*

# Template of Magento2
RUN set -xe \
    && cd / \
    && php -d memory_limit=-1 /composer.phar create-project --repository=https://repo.magento.com/ magento/project-community-edition:2.4.* mg24 \
    && cd mg24 \
    && tar -zcvf ../mg24.tar.gz * \
    && cd .. \
    && rm -rf mg24 \
    && /composer.phar clear-cache


WORKDIR /var/www/magento2

COPY entrypoint.sh /
RUN chmod 755 /entrypoint.sh

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
