# inovatyvus-driver

How to run:

1. Clone the repository
2. run `docker-compose up --build` in the root directory
3. run `docker exec -it inovatyvus-driver-laravel bash` to enter the container
5. run `cp .env.example .env` to create the environment file
6. run `php artisan key:generate` to generate the application key
7. run `composer install` to install dependencies
8. run `php app:init` to run migration and data seeding

