# linksink

## Requirements

- PHP 8.0 or higher
- Composer
- SQLite (or another database supported by Doctrine)

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/freifunk/linksink.git
    cd linksink
    ```

2. Install PHP dependencies:
    ```sh
    composer install
    ```

## Initializing the Database

1. Create the database schema:
    ```sh
    php bin/console doctrine:schema:create
    ```

## Running Tests

1. Run PHPUnit tests:
    ```sh
    ./vendor/bin/phpunit
    ```

## Additional Commands

- To clear the cache:
    ```sh
    php bin/console cache:clear
    ```

- To run the development server:
    ```sh
    php bin/console server:run
    ```