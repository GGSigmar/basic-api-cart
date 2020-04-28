# Basic Cart API
Simple API for managing products and carts.
Find source code at: https://github.com/GGSigmar/basic-api-cart

## Considerations:

* There can be up to 3 items in the cart
* The list of products must be paginated and 3 products per page must be displayed
* Adding a product to cart second time increases its quantity

## Installation

### Setting up API

* Download project from GitHub (*git clone git@github.com:GGSigmar/basic-api-cart.git*)
* Install project dependencies (*composer install*)
* Create project database (*bin/console doctrine:database:create*)
* Run project migrations to set up database (*bin/console doctrine:migrations:migrate*)
* Load fixtures for initial products (*bin/console doctrine:fixtures:load*)
* Start local server (if you have symfony binary: *symfony serve -d*, else *bin/console server:start*)

### Setting up tests
* Create test database (*bin/console doctrine:database:create --env=test*)
* Create test schema (*bin/console doctrine:schema:create --env=test*)
* Run tests (*bin/phpunit*, local server has to be running)
* Enjoy the green :D
