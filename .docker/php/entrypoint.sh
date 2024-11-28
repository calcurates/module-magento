#!/bin/sh
# see https://docs.docker.com/compose/startup-order/

set -e

until mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="3306" -e "SELECT VERSION()"; do
  >&2 echo "MySQL is unavailable - waiting"
  sleep 1
done

>&2 echo "MySQL is up"

isSourced=`mysql --silent --skip-column-names --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="3306" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$MYSQL_DATABASE';"`
#isSourced=0
if [[ -f "/mg24.tar.gz" || "${isSourced}" -eq "0" ]]; then
    echo "Copying the Magento2 template to the working directory..."
    rm -rf "vendor/*"
    tar -zxvf "/mg24.tar.gz"
    rm "/mg24.tar.gz"

    chmod 755 bin/magento

    # fix the magento. https://magento.stackexchange.com/questions/221002/magento-2-1-cli-install-command-failed
    bin/magento setup:uninstall

    bin/magento setup:install \
            --base-url=http://localhost/ \
            --backend-frontname=admin \
            --db-host=mysql:3306 \
            --db-name="$MYSQL_DATABASE" \
            --db-user="$MYSQL_USER" \
            --db-password="$MYSQL_PASSWORD" \
            --admin-firstname=Magento \
            --admin-lastname=User \
            --admin-email=user@example.com \
            --admin-user=admin \
            --admin-password=admin123 \
            --language=en_US \
            --currency=USD \
            --timezone=UTC \
            --use-rewrites=1 \
            --use-secure=0 \
            --search-engine=opensearch \
            --opensearch-host=opensearch \
            --opensearch-port=9200 \
            --opensearch-enable-auth=false

    bin/magento deploy:mode:set developer

    # bin/magento sampledata:deploy # not working. idk
    bin/magento setup:upgrade
    # disable TwoFactorAuth
    bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth
    bin/magento module:disable Magento_TwoFactorAuth
    # https://devdocs.magento.com/guides/v2.4/get-started/authentication/gs-authentication-token.html#integration-tokens
    bin/magento config:set oauth/consumer/enable_integration_as_bearer 1

    bin/magento cache:disable full_page
    bin/magento cache:flush

    echo "Changing the permissions..."
    find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
    find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
    chown -R :www-data .

    echo "Configuring the phpcs..."
    php vendor/bin/phpcs --config-set default_standard Magento2
    php vendor/bin/phpcs --config-set colors 1
    php vendor/bin/phpcs --config-set installed_paths /var/www/magento2/vendor/magento/magento-coding-standard/,/var/www/magento2/vendor/magento/php-compatibility-fork/PHPCompatibility
    php vendor/bin/phpcs --config-set severity 7
    php vendor/bin/phpcs --config-set show_progress 1

    echo "php ${PWD}/vendor/bin/phpcs ${PWD}/app/code/Calcurates/ModuleMagento -v" > /phpcs
    chmod 755 /phpcs


    echo "Configuring the php-cs-fixer..."
    curl -L -o /php-cs-fixer.phar https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.65.0/php-cs-fixer.phar
    chmod 755 /php-cs-fixer.phar
    echo "php /php-cs-fixer.phar fix ${PWD}/app/code/Calcurates/ModuleMagento --rules=@PSR12" > /php-cs-fixer
    chmod 755 /php-cs-fixer
fi

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
exec "$@"
