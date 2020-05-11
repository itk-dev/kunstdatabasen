# kunstdatabasen

## Dev install

```
# Start docker
itkdev-docker-compose up -d

# Run migrations
itkdev-docker-compose bin/console doctrine:migrations:migrate

# Load fixtures
itkdev-docker-compose bin/console hautelook:fixtures:load

# Create an admin user
# Choose ROLE_ADMIN for roles
itkdev-docker-compose bin/console app:create-user
```
