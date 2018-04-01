# cc-tutor-backend

> The public API is available at: https://cc-tutor-api.herokuapp.com/

> The application is available at: https://cc-tutor.herokuapp.com/

A compilation process visualization, interaction system and a compiler construction assistant.

This is the backend of the CC Tutor platform.

## Requirements

In order to locally setup the project, these must be included in your system:

- PHP
- Download and install VirtualBox: https://www.virtualbox.org/wiki/Downloads (used by Vagrant below).
- Download and install vagrant: https://www.vagrantup.com/ . Vagrant provides replicable development environments.
- Composer package manager for PHP: https://getcomposer.org/download/

## Development Setup

1. Clone the repo with all branches (the project follows git flow branching conventions)
2. Run `composer install`
3. In the `.env` file the `MAIL_USERNAME`, `MAIL_PASSWORD` and `MAIL_FROM_ADDRESS` to appropriate values.
4. The project contains a `Vagrantfile` in order to start a Homestead instance (replicable environment):
    - Run `vagrant up` to provision and start the environment
5. Run `vagrant ssh`
6. Run `php artisan migrate:refresh --seed`
7. Run `php artisan passport:install` and save the OAuth 2.0 keys in the `.env` following the `.env.example` format
8. Run `php artisan cctutor:install` to install the Java backend required for the Compiler Construction Assistant and the assignments.
9. You're good to go!

## Testing

To run the tests: `phpunit`
