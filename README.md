# kunstdatabasen

## Dev install

```
# Start docker
itkdev-docker-compose up -d

# Install
itkdev-docker-compose composer install

# Run migrations
itkdev-docker-compose bin/console doctrine:migrations:migrate

# Load fixtures
itkdev-docker-compose bin/console hautelook:fixtures:load

# Create an admin user
# Choose ROLE_ADMIN for roles
itkdev-docker-compose bin/console app:create-user
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
