#!/bin/sh
# Runs the php-cs-fixer installed with Magento's Composer dev dependencies.
# The `fix` command is implied.
set -eu

PHP_CS_FIXER_BIN=/var/www/magento2/vendor/bin/php-cs-fixer

if [ ! -f "$PHP_CS_FIXER_BIN" ]; then
    echo "php-cs-fixer not found: $PHP_CS_FIXER_BIN" >&2
    echo "Run 'docker compose up -d' so init installs Magento dependencies." >&2
    exit 1
fi

case "${1:-}" in
    --version|-V|--help|-h)
        exec php "$PHP_CS_FIXER_BIN" "$@"
        ;;
esac

# Also accept the explicit `fix` subcommand.
if [ "${1:-}" = "fix" ]; then
    shift
fi

cd /var/www/module-magento
exec php "$PHP_CS_FIXER_BIN" fix \
    --diff \
    --using-cache=no \
    --no-interaction \
    "$@"
