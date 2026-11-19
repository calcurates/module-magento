#!/bin/sh
# Scans only the module repository: .docker contains the whole Magento tree.
set -eu
cd /var/www/magento2
exec php vendor/bin/phpcs /var/www/module-magento --ignore='*/vendor/*,*/.docker/*,.git/*' "$@"
