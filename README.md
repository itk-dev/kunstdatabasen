# Kunstdatabasen

## Production

## Upgrade from version 1.5.2

```sh
# Upgrade migrations
itkdev-docker-compose bin/console doctrine:migrations:list
itkdev-docker-compose bin/console doctrine:migrations:sync-metadata-storage
itkdev-docker-compose bin/console doctrine:migrations:version --add --all --no-interaction
itkdev-docker-compose bin/console doctrine:migrations:list
itkdev-docker-compose-server pull
itkdev-docker-compose-server up --detach --remove-orphans
itkdev-docker-compose-server exec phpfpm composer install --no-dev --no-scripts
# Upgrade migrations (only once)
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:list
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:sync-metadata-storage
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:version --add --all --no-interaction
# Remove our single new migration
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:version --delete 'DoctrineMigrations\Version20230801120357' --no-interaction
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:list
```

## Install/update

```sh
# Edit .env.local
APP_SECRET=…

SUPPORT_MAIL=…
SITEIMPROVE_KEY=…
WEB_ACCESSIBILITY_STATEMENT_URL=…

# Install
itkdev-docker-compose-server pull
itkdev-docker-compose-server up --detach --remove-orphans
itkdev-docker-compose-server exec phpfpm composer install --no-dev
itkdev-docker-compose-server exec phpfpm bin/console doctrine:migrations:migrate --no-interaction

# Build assets using our development setup
docker compose run --rm node yarn install
docker compose run --rm node yarn build
# Clean up
rm -fr node_modules

itkdev-docker-compose-server exec phpfpm bin/console cache:clear --no-interaction
```

This site comes with an docker setup to do local developement.

## Running the docker setup

The default `.env` file that comes with the project is configured out-of-the-box
to match the docker setup.

```sh
# Create the frontend network if it does not already exist.
docker network inspect frontend 2>&1 > /dev/null || docker network create frontend
docker compose pull
docker compose up --detach

# Install
docker compose exec phpfpm composer install

# Run migrations
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

### Load fixtures

To ease the development on local setup the project supplies fixtures:

```sh
docker compose exec phpfpm bin/console hautelook:fixtures:load --no-bundles
```

### Refresh tags

```sh
docker compose exec phpfpm bin/console app:refresh-tags
```

### Open site

```sh
open "http://$(docker compose port nginx 8080)"
```

Open the administration interface:

```sh
open "http://$(docker compose port nginx 8080)/admin"
```

Sign in as `admin@example.com` with password `admin` if fixtures have been
loaded.

### Create an admin user

```sh
docker compose exec phpfpm bin/console app:create-user
```

### Browsersync

For testing and to auto-reload browser you can run
[Browsersync](https://browsersync.io/) with

```sh
browser-sync start --config bs-config.js
```

## Build the frontend

We use [Webpack
Encore](https://symfony.com/doc/current/frontend.html#frontend-webpack-encore)
to build frontend assets.

```sh
# Install dependencies
docker compose run --rm node yarn install
# Build assets
docker compose run --rm node yarn build
```

During development you can watch for changes:

```sh
docker compose run --rm node yarn watch
```

## Migration in production

Run the migration command:

```sh
docker compose exec phpfpm bin/console app:import-spreadsheet var/migration.xls
docker compose exec phpfpm bin/console app:refresh-tags
```

Add image files in a folder (`public/images/migration_images`) each named after
the inventoryId they match, eg.`1000.jpg`.

Attach images to Items

```sh
docker compose exec phpfpm bin/console app:import-images public/images/migration_images
```

Build frontend assets:

```sh
docker compose run --rm node yarn build
```

### Coding standards

```sh
docker compose exec phpfpm composer coding-standards-check
```

```sh
docker compose run --rm prettier 'assets/' --write
docker compose run --rm prettier 'assets/' --check
```
