# Kunstdatabasen

This site comes with an docker setup to do local developement.

## Running the docker setup

The default `.env` file that comes with the project is configured out-of-the-box
to match the docker setup.

```sh
docker compose up --detach

# Install
docker compose exec phpfpm composer install

# Run migrations
docker compose exec phpfpm bin/console doctrine:migrations:migrate
```

### Load fixtures

To easy the development on local setup the project also supplies fixtures.

```sh
docker compose exec phpfpm bin/console hautelook:fixtures:load
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

For testing and to autoreload browser you kan run browsersync with `browser-sync
start --config bs-config.js`

## Build the front end

The frontend is using web-pack and yarn to handle packages. First install the packages.

```sh
docker compose run --rm node yarn install
docker compose run --rm node yarn build
```

For development use

```sh
docker compose run --rm node yarn watch
```

## Production

Automatic deployment to `stg` and `prod` are set up as [Github Actions](https://github.com/aakb/kunstdatabasen/actions).

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
