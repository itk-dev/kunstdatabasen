# Kunst databasen
This site comes with an docker setup to do local developement.


## Running the docker setup

We recommand that you use the tool set in https://github.com/aakb/itkdev-docker if you don't want to you need to manual create
the frontend network used in the docker-compose file (or remove it).

The default `.env` file that comes with the projekt is configured out-of-the-box to match the docker setup.

```sh
docker-compose up -d
```

### Run migrations
To setup the database we use doctrin migrations, so execute them first.

```sh
itkdev-docker-compose bin/console doctrine:migrations:migrate
```

### Load fixtures
To easy the development on local setup the project also supplies fixtures.

```
itkdev-docker-compose bin/console hautelook:fixtures:load
```

### Create an admin user

```sh
itkdev-docker-compose bin/console app:create-user
```

## Build the front end
The frontend is using web-pack and yarn to handle packages. First install the packages.

```sh
docker-compose run yarn install
```

In development use.
```sh
docker-compose run yarn watch
```

## Production

@TODO: Describe production setup and deployment.

## Migration in production

Run the migration command:
```
bin/console app:import-spreadsheet var/migration.xls
bin/console app:refresh-tags
```

Add image files in a folder (`public/images/migration_images`) each named after the inventoryId they match, eg.`1000.jpg`.

Attach images to Items
```
bin/console app:import-images public/images/migration_images
```

Build the front end.
```sh
docker-compose run yarn build
```