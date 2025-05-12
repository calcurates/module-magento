FROM php:8.4-fpm-alpine

# Update repo and install some utilites
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk --no-cache add htop unzip gzip tar curl linux-headers mariadb-connector-c

# PHP
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
\
    # Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
\
    # Sodium
    && apk add --no-cache libsodium-dev \
    && docker-php-ext-install sodium \
    && docker-php-ext-enable sodium \
\
    # ZIP
    && apk add --no-cache libzip libzip-dev \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip \
\
    # XSLT
    && apk add --no-cache libxslt-dev libxslt \
    && docker-php-ext-install xsl \
    && docker-php-ext-enable xsl \
\
    # INTL
    && apk add --no-cache icu-dev icu-libs \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \
\
    # SOAP
    && apk add --no-cache libxml2-dev \
    && docker-php-ext-install soap \
    && docker-php-ext-enable soap \
\
    # Bcmath
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \
\
    # Mysql
    && apk add --no-cache mysql-client \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
\
    # Sockets
    && docker-php-ext-install sockets \
    && docker-php-ext-enable sockets \
\
    # GD
    && apk add --no-cache freetype libpng libwebp libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd \
        --enable-gd \
        --with-freetype \
        --with-webp \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
\
    # PCNTL
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl \
\
    # Ftp
    && docker-php-ext-install ftp \
    && docker-php-ext-enable ftp

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

# Composer
# https://github.com/markshust/docker-magento/issues/1084
COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/.composer
COPY auth.json /.composer/

# Template of Magento2
RUN set -xe \
    && cd / \
    && curl https://codeload.github.com/magento/magento2/tar.gz/refs/tags/2.4.8 --output magento2-2.4.8.tar.gz


WORKDIR /var/www/magento2

COPY entrypoint.sh /
RUN chmod 755 /entrypoint.sh

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
