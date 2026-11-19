#!/bin/sh
# Runs as www-data: installs Magento when the database is empty and applies
# module updates on every start.
set -eu

cd /var/www/magento2

if [ ! -f bin/magento ]; then
    echo "Extracting Magento ${MAGENTO_VERSION}..."
    tar -zxf /magento2.tar.gz --strip-components=1
    chmod 755 bin/magento
fi

if [ ! -f vendor/autoload.php ]; then
    echo "Installing composer dependencies..."
    php -d memory_limit=-1 -d default_socket_timeout=1000 /usr/local/bin/composer install --no-interaction
fi

# The database is the source of truth for "Magento is installed": both the
# MySQL and OpenSearch volumes can be reset independently of the Magento tree.
tables=$(php -d display_errors=0 -r '
    $pdo = new PDO("mysql:host=mysql;port=3306", getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?");
    $stmt->execute([getenv("MYSQL_DATABASE")]);
    echo (int) $stmt->fetchColumn();
' 2>/dev/null || echo 0)

if [ "$tables" -eq 0 ]; then
    echo "Installing Magento..."
    rm -f app/etc/env.php app/etc/config.php
    rm -rf generated/code generated/metadata var/cache/* var/page_cache/*

    bin/magento setup:install \
        --base-url="http://localhost:${ANGIE_PORT:-80}/" \
        --backend-frontname=admin \
        --db-host=mysql:3306 \
        --db-name="$MYSQL_DATABASE" \
        --db-user="$MYSQL_USER" \
        --db-password="$MYSQL_PASSWORD" \
        --admin-firstname=Magento \
        --admin-lastname=User \
        --admin-email="${MAGENTO_ADMIN_EMAIL:-user@example.com}" \
        --admin-user="${MAGENTO_ADMIN_USER:-admin}" \
        --admin-password="${MAGENTO_ADMIN_PASSWORD:-admin123}" \
        --language=en_US \
        --currency=USD \
        --timezone=UTC \
        --use-rewrites=1 \
        --use-secure=0 \
        --use-secure-admin=0 \
        --search-engine=opensearch \
        --opensearch-host=opensearch \
        --opensearch-port=9200 \
        --opensearch-enable-auth=false \
        --no-interaction

    bin/magento deploy:mode:set developer
    bin/magento config:set oauth/consumer/enable_integration_as_bearer 1
    bin/magento cache:disable full_page
elif [ ! -f app/etc/env.php ]; then
    echo "The database already has Magento tables, but app/etc/env.php is missing." >&2
    echo "Recreate the database with 'docker compose down -v'." >&2
    exit 1
fi

if [ "$tables" -eq 0 ] && [ "${MAGENTO_SAMPLE_DATA:-0}" = "1" ] && \
    ! grep -q '"magento/module-catalog-sample-data"' composer.json; then
    echo "Deploying Magento sample data..."
    composer config repositories.magento composer https://repo.magento.com/
    if ! bin/magento sampledata:deploy --no-interaction; then
        composer config --unset repositories.magento >/dev/null 2>&1 || true
        echo "WARNING: sample data deployment failed." >&2
        echo "Check the Marketplace keys in .docker/php/auth.json." >&2
    fi
fi

echo "Applying module updates..."
bin/magento setup:upgrade --no-interaction
bin/magento cache:flush

echo "Configuring the code style tools..."
php vendor/bin/phpcs --config-set default_standard Magento2
php vendor/bin/phpcs --config-set colors 1
php vendor/bin/phpcs --config-set installed_paths "$PWD/vendor/magento/magento-coding-standard/,$PWD/vendor/magento/php-compatibility-fork/PHPCompatibility"
php vendor/bin/phpcs --config-set severity 7
php vendor/bin/phpcs --config-set show_progress 1
