# Local development with Docker

Run from the repository root; Compose reads `.env` automatically.

## First run

```bash
# once per machine: shared network with the other Calcurates projects
docker network create calcurates-dev

cp .env.dist .env
cp .docker/php/auth.json.dist .docker/php/auth.json

docker compose up -d --build
```

`init` installs Magento on the first start and runs `setup:upgrade` on every start.

- Storefront: `http://localhost:${ANGIE_PORT:-80}/`
- Admin: `http://localhost:${ANGIE_PORT:-80}/admin` — credentials from `.env`
  (`admin` / `admin123` by default)

## Paths

| Container path              | Host path             |
|-----------------------------|-----------------------|
| `/var/www/module-magento`   | repository root       |
| `/var/www/magento2`         | `.docker/magento2`    |

PhpStorm: add `.docker/magento2` as a content root; these two paths are also the Xdebug mappings.

## Commands

```bash
# Magento
docker compose exec php bin/magento cache:flush
docker compose exec -w /var/www/magento2 php composer update --no-interaction

# code style
docker compose exec php phpcs
docker compose exec php php-cs-fixer --dry-run  # check
docker compose exec php php-cs-fixer            # fix

# reset MySQL and OpenSearch, keep Magento files
docker compose down -v
docker compose up -d

# full Magento reinstall
docker compose down -v
rm -rf .docker/magento2
docker compose up -d --build
```

## Notes

- Xdebug: set `XDEBUG_MODE=debug` and `XDEBUG_PORT=9003` in `.env`.
- `MAGENTO_ADMIN_*` in `.env` is used only by the first `setup:install`;
  changing it later does not update the existing admin user.
- `LOCAL_UID`/`LOCAL_GID` in `.env` must match `id -u`/`id -g`. The init
  container fixes ownership of `.docker/magento2` once.
- Magento sample data is installed by default on the first setup
  (`MAGENTO_SAMPLE_DATA=1`). It needs valid Marketplace keys in
  `.docker/php/auth.json`; otherwise init logs a warning and continues without it.
  Set `MAGENTO_SAMPLE_DATA=0` to skip it.
- `git clean -xfd` deletes `.docker/magento2` and forces a Magento reinstall.
