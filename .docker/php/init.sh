#!/bin/sh
# One-shot preparation, runs as root before php/angie start.
set -eu

MAGENTO_DIR=/var/www/magento2
MODULE_SRC_DIR=/var/www/module-magento
MODULE_DIR="$MAGENTO_DIR/app/code/Calcurates/ModuleMagento"
PHP_USER=www-data

mkdir -p "$MAGENTO_DIR" "$(dirname "$MODULE_DIR")" /var/www/.composer
chown "$PHP_USER:$PHP_USER" /var/www/.composer

# Magento lives on the host, so Docker may have created the directory as root.
# Do it once per Magento tree: a recursive chown on every start is slow.
if [ ! -f "$MAGENTO_DIR/.docker-init" ]; then
    chown -R "$PHP_USER:$PHP_USER" "$MAGENTO_DIR"
    touch "$MAGENTO_DIR/.docker-init"
fi

# Magento finds the module repository as app/code/Calcurates/ModuleMagento.
if [ -d "$MODULE_DIR" ] && [ ! -L "$MODULE_DIR" ]; then
    rm -rf "$MODULE_DIR"
fi
ln -sfn "$MODULE_SRC_DIR" "$MODULE_DIR"

exec runuser -u "$PHP_USER" -- sh "$MODULE_SRC_DIR/.docker/php/init-app.sh"
