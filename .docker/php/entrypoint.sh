#!/bin/sh
# see https://docs.docker.com/compose/startup-order/

set -e

until mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="3306" -e "SELECT VERSION()"; do
  >&2 echo "MySQL is unavailable - waiting"
  sleep 1
done

>&2 echo "MySQL is up"

isSourced=`mysql --silent --skip-column-names --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="3306" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$MYSQL_DATABASE';"`

if [ "$isSourced" -eq "0" ]; then
    echo "Copying the Magento2 template to the working directory..."
    tar -zxvf /templates/mg234.tar.gz

    chmod 755 bin/magento

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
            --timezone=Europe/Minsk \
            --use-rewrites=1 \
            --use-secure=0

    bin/magento deploy:mode:set developer

    # bin/magento sampledata:deploy # not working. idk
    bin/magento setup:upgrade
    bin/magento cache:clean

    echo "Changing the file permissions..."
    find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
    find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
    chown -R :www-data .

    echo "Config phpcs..."
    php vendor/bin/phpcs --config-set default_standard Magento2
    php vendor/bin/phpcs --config-set colors 1
    php vendor/bin/phpcs --config-set installed_paths /var/www/magento2/vendor/magento/magento-coding-standard/
    php vendor/bin/phpcs --config-set severity 8
    php vendor/bin/phpcs --config-set show_progress 1

    echo "php ${PWD}/vendor/bin/phpcs ${PWD}/app/code/Calcurates/ModuleMagento -v" > /phpcs
    chmod 755 /phpcs
fi

# avoid stupid docker initialization
# see https://github.com/docker/compose/issues/1809
exec "$@"
