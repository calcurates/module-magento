# Debian instead of Alpine: this is what Magento officially supports and it has
# runuser/usermod, so the container can share the host UID/GID.
FROM php:8.5-fpm

ARG MAGENTO_VERSION=2.4.9
ARG LOCAL_UID=1000
ARG LOCAL_GID=1000

ENV COMPOSER_HOME=/var/www/.composer \
    MAGENTO_VERSION=${MAGENTO_VERSION}

RUN set -xe \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        default-mysql-client \
        git \
        procps \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PIE/mlocati instead of a hand-written apk list: it pulls the system
# dependencies for bundled extensions and keeps the Dockerfile readable.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN set -xe \
    && install-php-extensions \
        bcmath \
        ftp \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        soap \
        sockets \
        sodium \
        xdebug \
        xsl \
        zip \
    \
    # Magento template. It stays in the image, so a recreated init container
    # still has it after the site is installed.
    && curl -fsSL \
        "https://codeload.github.com/magento/magento2/tar.gz/refs/tags/${MAGENTO_VERSION}" \
        --output /magento2.tar.gz \
    \
    # Run as the host user, so files created in the bind mounts are owned by it.
    && groupmod -o -g "${LOCAL_GID}" www-data \
    && usermod -o -u "${LOCAL_UID}" -g "${LOCAL_GID}" www-data \
    && mkdir -p "${COMPOSER_HOME}" \
    \
    # php-fpm cannot setuid when it runs as www-data already.
    && sed -i '/^user = /d; /^group = /d' /usr/local/etc/php-fpm.d/www.conf

COPY auth.json /var/www/.composer/

RUN chown -R www-data:www-data "${COMPOSER_HOME}"

# `docker compose exec php phpcs` and `php-cs-fixer` shortcuts for the module.
# php-cs-fixer itself comes from Magento's Composer dev dependencies.
COPY phpcs.sh /usr/local/bin/phpcs
COPY php-cs-fixer.sh /usr/local/bin/php-cs-fixer
RUN chmod 755 /usr/local/bin/phpcs /usr/local/bin/php-cs-fixer

WORKDIR /var/www/magento2

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
