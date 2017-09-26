Brewerydb API Search
====================

Simple application that uses the Brewerydb API services to search Beers and Breweries.

### Were used:

- PHP 7
- Silex 2 - Micro-framework based on the Symfony 
- Pimple  - Dependency Injection Container
- Pintlabs Brewerydb API
- Twig
- Ajax
- JQuery

### Installation & Usage

.. code-block:: console

    $ cd path/to/install
    $ php composer.phar install

* Start the PHP built-in web server with the command:

.. code-block:: console

    $ php -S localhost:8080 -t web web/index.php

* Then, browse to http://localhost:8080



Improvements to the next version:
-----------------------------------

* Redis Cache
* Use React on the front-end
* Autocomplete in search

