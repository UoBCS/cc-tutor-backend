# cc-tutor-backend

A compilation process visualization, interaction system and a compiler construction assistant.

This is the backend of the CC Tutor platform.

## Development Setup

1. Clone the repo with all branches (the project follows git flow branching conventions)
2. Run `composer install`
3. The project contains a `Vagrantfile` in order to start a Homestead instance:
    - Run `vagrant up` to provision and start the environment
4. Run `vagrant ssh`
5. Run `php artisan migrate:refresh --seed`
6. Run `php artisan passport:install` and save the OAuth 2.0 keys in the `.env` following the `.env.example` format
8. Run `php artisan cctutor:install` to install the Java backend required for the Compiler Construction Assistant and the assignments.
7. You're good to go!

## Testing

To run the tests: `phpunit`
