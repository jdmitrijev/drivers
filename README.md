# inovatyvus-driver-Counseling

How to run:

1. Clone the repository
2. run `docker-compose up --build` in the root directory
3. run `docker exec -it inovatyvus-driver-laravel bash` to enter the container
4. run `cd src` to enter the src directory
5. run `cp .env.example .env` to create the environment file
6. run `php artisan key:generate` to generate the application key
7. run `composer install` to install dependencies
8. run `php artisan migrate` to run migrations
9. run `php artisan db:seed` to seed the database

# API

## Book Appointment
/api/appointment
### Method: POST
### Body:
```json
{
    "employee_id": "int", (from 1 to 10)
    "start_time": "2025-05-23 10:00:00", (in format YYYY-MM-DD HH:MM:SS)
    "duration": "30" (in minutes)
    "notes": "string" (optional)
}
```

To run tests please run `./vendor/bin/pest` in src directory


# Annotations / Presumptions
In the requirements the naming was used sessions, but it was changed to Appointments.
Presuming we follow proper terminology, and it would not interfere with computer "session" naming.

Another presumption is that for booking registration employee is already in the system and has ID provided to him.
This would help to speed up registration. And inside the documentation it was not specified exactly how to register.